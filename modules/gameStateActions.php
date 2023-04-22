<?php

/**
 *
 * @author Lunalol
 */
trait gameStateActions
{
	function acCountry(int $country, bool $auto = false)
	{
		if (!$auto) $this->checkAction('country');
//
		if (!in_array($country, array_keys($this->COLORS))) throw new BgaVisibleSystemException('Invalid country:' . $country);
		$color = $this->COLORS[$country];
//
		$player_id = self::getActivePlayerId();
		self::DbQuery("UPDATE player SET country = $country, player_color = '$color' WHERE player_id = $player_id");
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('choose_country', clienttranslate('${player_name} will play ${COUNTRY}'), [
			'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'color' => $color
		]);
		self::reloadPlayersBasicInfos();
//* -------------------------------------------------------------------------------------------------------- */
		$this->gamestate->nextState('nextPlayer');
	}
	function acHunt(int $prey, array $hunters)
	{
		$this->checkAction('hunt');
//
		$player_id = self::getActivePlayerId();
		$country = self::getUniqueValueFromDB("SELECT country FROM player WHERE player_id = $player_id");
//
// Abandon hunters
//
		foreach ($this->animals->getCardsInLocation('prey') as $animal)
		{
			if ($animal['id'] != $prey)
			{
				$abandon = $this->hunters->getCardsInLocation($animal['id'], $player_id);
				foreach ($abandon as $hunter) $this->hunters->moveCard($hunter['id'], 'discard', $hunter['location_arg']);
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('remove_hunters', '', ['hunters' => $abandon]);
//* -------------------------------------------------------------------------------------------------------- */
				if (+$this->hunters->countCardsInLocation($animal['id']) === 0)
				{
					$this->animals->moveCard($animal['id'], 'deck');
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('remove_animal', clienttranslate('${ANIMAL} (${VALUE}) hunt is abandoned'), [
						'i18n' => ['ANIMAL'], 'ANIMAL' => $this->PREY[$animal['type']],
						'VALUE' => $animal['type_arg'],
						'animal' => $animal,
					]);
//* -------------------------------------------------------------------------------------------------------- */
				}
			}
		}
//
// Get animal or reveal a new one from deck
//
		if ($prey === 0)
		{
//
// Reveal animal
//
			$animal = $this->animals->pickCardForLocation('deck', 'prey');
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('place_animal', clienttranslate('${ANIMAL} (${VALUE}) is revealed'), [
				'i18n' => ['ANIMAL'], 'ANIMAL' => $this->PREY[$animal['type']],
				'VALUE' => $animal['type_arg'],
				'animal' => $animal,
			]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		else $animal = $this->animals->getCard($prey);
		if (is_null($animal)) throw new BgaVisibleSystemException('Invalid animal: ' . $prey);
//
// Play hunters
//
		$values = [];
		foreach ($hunters as $hunter)
		{
			$card = $this->hunters->getCard($hunter);
			if (is_null($card)) throw new BgaVisibleSystemException('Invalid hunter: ' . $hunter);
			if ($card['location_arg'] !== $player_id) throw new BgaVisibleSystemException('Invalid player: ' . $card['location_arg'] . ' vs ' . $player_id);
			$this->hunters->moveCard($hunter, $animal['id'], $player_id);
			$values[] = $card['type_arg'];
		}
//
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('place_hunters', clienttranslate('${player_name} plays hunter(s): <B>${HUNTERS}</B>'), [
			'player_name' => $this->getActivePlayerName(),
			'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
			'HUNTERS' => implode(', ', $values),
			'hunters' => $this->hunters->getCardsInLocation($animal['id']), 'animal' => $animal,
		]);
//* -------------------------------------------------------------------------------------------------------- */
//
// Compute hunt value
//
		$value = array_sum(array_column($this->hunters->getCardsInLocation($animal['id']), 'type_arg'));
//
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', clienttranslate('${player_name} hunts with a value of <B>${VALUE}</B>'), [
			'player_name' => $this->getActivePlayerName(),
			'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
			'VALUE' => $value,
		]);
//* -------------------------------------------------------------------------------------------------------- */
//
		if ($value >= $animal['type_arg'])
		{
			$this->incStat(1, $this->PREY[$animal['type']], $player_id);
//
			foreach ($this->hunters->getCardsInLocation($animal['id']) as $hunter)
			{
//* -------------------------------------------------------------------------------------------------------- */
				$this->hunters->moveCard($hunter['id'], 'discard', $hunter['location_arg']);
				self::notifyAllPlayers('remove_hunters', '', ['hunters' => [$hunter]]);
//* -------------------------------------------------------------------------------------------------------- */
			}
//* -------------------------------------------------------------------------------------------------------- */
			$this->animals->moveCard($animal['id'], 'killed', $player_id);
			self::notifyAllPlayers('remove_animal', clienttranslate('${COUNTRY} wins a ${ANIMAL} (${VALUE})'), [
				'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
				'i18n' => ['ANIMAL'], 'ANIMAL' => $this->PREY[$animal['type']],
				'VALUE' => $animal['type_arg'],
				'animal' => $animal, 'player_id' => $player_id
			]);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('update_counts', '', ['player_id' => $player_id, 'counts' => self::counts($player_id)]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		else
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('${COUNTRY} tracks a ${ANIMAL} (${VALUE})'), [
				'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
				'i18n' => ['ANIMAL'], 'ANIMAL' => $this->PREY[$animal['type']],
				'VALUE' => $animal['type_arg'],
			]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('update_decks', '', [
			'animals' => $this->animals->countCardsInLocations(),
			'hands' => $this->hunters->countCardsByLocationArgs('hand'),
			'discards' => $this->hunters->countCardsByLocationArgs('discard'),
		]);
//* -------------------------------------------------------------------------------------------------------- */
		$counts = self::counts($player_id);
		foreach ($counts as $prey => $count) if ($prey !== 'Fox' && ($count[0] + $counts['Fox'][0]) >= $count[1]) return $this->gamestate->nextState('gameEnd');
//
		$this->gamestate->nextState('nextPlayer');
	}
	function acPurchase($animals)
	{
		if (!$animals)
		{
			$this->checkAction('pass');
			return $this->gamestate->nextState('nextPlayer');
		}
//
		$player_id = self::getActivePlayerId();
		$country = self::getUniqueValueFromDB("SELECT country FROM player WHERE player_id = $player_id");
//
		$value = 0;
		foreach ($animals as $prey)
		{
			$this->animals->moveCard($prey, 'discard');
//
			$animal = $this->animals->getCard($prey);
			$value += $animal['type'] == FOXES ? 16 : $animal['type_arg'];
//
			$this->incStat(1, $this->PREY[$animal['type']] . '_sold', $player_id);
//
			while ($value >= 8)
			{
				$value -= 8;
//
				$dog = $this->hunters->pickCardForLocation('deck', 'hand', $player_id);
				if ($dog)
				{
					$this->incStat(1, 'dogs', $player_id);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('${COUNTRY} buys a dog (${VALUE})'), [
						'COUNTRY' => ['log' => $this->coloredCountryName($country), 'args' => ['country' => $this->COUNTRIES[$country], 'i18n' => ['country']]],
						'VALUE' => $dog['type_arg'],
					]);
//* -------------------------------------------------------------------------------------------------------- */
				}
				else throw new BgaUserException(self::_('Not enough dog(s)available'));
			}
		}
//
		self::notifyAllPlayers('update_counts', '', ['player_id' => $player_id, 'counts' => self::counts($player_id)]);
//
		self::notifyAllPlayers('update_decks', '', [
			'hands' => $this->hunters->countCardsByLocationArgs('hand'),
			'dogs' => $this->hunters->countCardsByLocationArgs('dogs'),
		]);
//
		$this->gamestate->nextState('nextPlayer');
	}
}
