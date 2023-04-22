<?php

/**
 *
 * @author Lunalol
 */
trait gameStates
{
	function stGameStart()
	{
//
// Create deck of animals and shuffle it
//
		$this->animals->createCards($this->ANIMALS, 'deck');
		$this->animals->shuffle('deck');
//
// Create deck of dogs and shuffle it
//
		$this->hunters->createCards($this->DOGS, 'deck');
		$this->hunters->shuffle('deck');
		if (self::getGameStateValue('countries') != 1) return $this->gamestate->nextState('setup');
//
//* -------------------------------------------------------------------------------------------------------- */
		$this->notifyAllPlayers('message', '<span class="TGAFH-Turn">${txt}</span>', ['i18n' => ['txt'], 'txt' => clienttranslate('Competing countries choice')]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->gamestate->nextState('countryChoice');
	}
	function stCountries()
	{
		$player_id = $this->activeNextPlayer();
		if ($player_id != self::getGameStateValue('firstPlayer'))
		{
			$this->gamestate->nextState('countryChoice');
//
			$countries = self::argCountryChoice()['countries'];
			if (sizeof($countries) === 1) self::acCountry(array_keys($countries)[0], true);
//
		}
		else $this->gamestate->nextState('setup');
	}
	function stSetup()
	{
//
// Create deck of hunters for each player and assign country
//
		foreach ($this->loadPlayersBasicInfos() as $player_id => $player)
		{
			$country = array_search($player['player_color'], $this->COLORS);
			$this->hunters->createCards($this->HUNTERS, 'hand', $player_id);
			self::dBQuery("UPDATE player SET country = $country WHERE player_id = $player_id");
		}
		self::DbQuery("UPDATE hunters JOIN player ON card_location_arg = player_id SET card_type = country");
//* -------------------------------------------------------------------------------------------------------- */
		$this->notifyAllPlayers('msg', '<span class="TGAFH-Turn">${txt}</span>', ['i18n' => ['txt'], 'txt' => clienttranslate('Start of round')]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->gamestate->nextState('startOfHuntingRound');
	}
	function stStartOfHuntingRound()
	{
//
// Go to next player if current player hand is empty
//
		$player_id = $this->getActivePlayerId();
		while ($this->hunters->countCardInLocation('hand', $player_id) == 0) $player_id = $this->activeNextPlayer();
//
//
		$this->gamestate->nextState('playerHuntingRound');
	}
	function stEndOfHuntingRound()
	{
//
// Go to between rounds if all hands are empty
//
		if ($this->hunters->countCardInLocation('hand') > 0)
		{
			$this->activeNextPlayer();
			return $this->gamestate->nextState('startOfHuntingRound');
		}
//
// Discards all hunters and preys
//
		foreach ($this->animals->getCardsInLocation('prey') as $animal)
		{
			$hunters = $this->hunters->getCardsInLocation($animal['id']);
			foreach ($hunters as $hunter) $this->hunters->moveCard($hunter['id'], 'discard', $hunter['location_arg']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('remove_hunters', '', ['hunters' => $hunters]);
//* -------------------------------------------------------------------------------------------------------- */
			$this->animals->moveCard($animal['id'], 'deck');
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('remove_animal', '', ['animal' => $animal]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//
// Restore hands of hunters/dogs
//
		$this->hunters->moveAllCardsInLocationKeepOrder('discard', 'hand');
//* -------------------------------------------------------------------------------------------------------- */
		$this->notifyAllPlayers('update_decks', '<span class="TGAFH-Turn">${txt}</span>', ['i18n' => ['txt'], 'txt' => clienttranslate('Between rounds'),
			'animals' => $this->animals->countCardsInLocations(),
			'hands' => $this->hunters->countCardsByLocationArgs('hand'),
			'discards' => $this->hunters->countCardsByLocationArgs('discard'),
		]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->gamestate->changeActivePlayer(self::getGameStateValue('firstPlayer'));
		$this->gamestate->nextState('startOfPurchaseRound');
	}
	function stStartOfPurchaseRound()
	{
		$player_id = $this->getActivePlayerId();
//
		$value = 0;
		foreach ($this->animals->getCardsInLocation('killed', $player_id) as $animal) $value += $animal['type'] == FOXES ? 16 : $animal['type_arg'];
		if ($value < 8)
		{
			$country = self::getUniqueValueFromDB("SELECT country FROM player WHERE player_id = $player_id");
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('${COUNTRY} can\'t buy any dog'), [
				'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
			]);
			return $this->gamestate->nextState('nextPlayer');
//* -------------------------------------------------------------------------------------------------------- */
		}
//
		$this->gamestate->nextState('playerPurchaseRound');
	}
	function stEndOfPurchaseRound()
	{
		$player_id = $this->activeNextPlayer();
		if ($player_id != self::getGameStateValue('firstPlayer')) return $this->gamestate->nextState('startOfPurchaseRound');
//* -------------------------------------------------------------------------------------------------------- */
		$this->notifyAllPlayers('message', '<span class = "TGAFH-Turn">${txt}</span>', ['i18n' => ['txt'], 'txt' => clienttranslate('End of round')]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->incStat(1, 'rounds');
//
		$max = -1;
		$firstPlayer = null;
//
		foreach (array_keys($this->loadPlayersBasicInfos()) as $player_id)
		{
//
			$this->incStat(1, 'rounds', $player_id);
// Value of animals from hunting
			$total = 0;
			foreach ($this->animals->getCardsInLocation('killed', $player_id) as $animal) $total += $animal['type_arg'];
			$total *= 100;
// Quantity
			$total += $this->animals->countCardsInLocation('killed', $player_id);
// Bears / Deers / Hares / Squirel
			foreach ([BEARS, DEERS, HARES, SQUIRRELS] as $animal)
			{
				$total *= 100;
				$total += sizeof($this->animals->getCardsOfTypeInLocation($animal, null, 'killed', $player_id));
			}
			if ($total > $max)
			{
				$max = $total;
				$firstPlayer = $player_id;
			}
		}
//
		$country = self::getUniqueValueFromDB("SELECT country FROM player WHERE player_id = $firstPlayer");
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', clienttranslate('${COUNTRY} will play first'), [
			'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
		]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->notifyAllPlayers('message', '<span class = "TGAFH-Turn">${txt}</span>', ['i18n' => ['txt'], 'txt' => clienttranslate('Start of round')]);
//* -------------------------------------------------------------------------------------------------------- */
//
		self::setGameStateValue('firstPlayer', $firstPlayer);
		$this->gamestate->changeActivePlayer($firstPlayer);
		$this->gamestate->nextState('startOfHuntingRound');
	}
}
