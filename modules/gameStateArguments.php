<?php

/**
 *
 * @author Lunalol
 */
trait gameStateArguments
{
	function argCountryChoice()
	{
		return ['countries' => array_diff_key($this->COUNTRIES, self::getCollectionFromDB("SELECT country, player_color FROM player", true))];
	}
	function argPlayerHuntingRound()
	{
		$private = [];
		foreach (array_keys($this->loadPlayersBasicInfos()) as $player_id)
		{
			$hand = $this->hunters->getPlayerHand($player_id);
			array_multisort(array_column($hand, 'type_arg'), $hand);
			$private[$player_id]['hand'] = $hand;
		}
//
		return ['_private' => $private,];
	}
	function argPlayerPurchaseRound()
	{
		$private = [];
		foreach (array_keys($this->loadPlayersBasicInfos()) as $player_id)
		{
			$hand = $this->hunters->getPlayerHand($player_id);
			array_multisort(array_column($hand, 'type_arg'), $hand);
			$private[$player_id]['hand'] = $hand;
//
			$killed = $this->animals->getCardsInLocation('killed', $player_id);
			array_multisort(array_column($killed, 'type_arg'), $killed);
			$private[$player_id]['animals'] = $killed;
		}
//
		return ['_private' => $private,];
	}
}
