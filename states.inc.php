<?php

$machinestates = [
// The initial state. Please do not modify.
	1 => [
		"name" => "gameSetup",
		"description" => "",
		"type" => "manager",
		"action" => "stGameSetup",
		"transitions" => ["" => 10]
	],
	10 => array(
		"name" => "gameStart",
		"description" => '',
		"type" => "game",
		"action" => "stGameStart",
		"transitions" => ["countryChoice" => 20, "setup" => 50]
	),
	20 => array(
		"name" => "countryChoice",
		"description" => clienttranslate('${actplayer} must choice a competing country'),
		"descriptionmyturn" => clienttranslate('${you} must choice a competing country'),
		"type" => "activeplayer",
		'args' => 'argCountryChoice',
		"possibleactions" => array("country"),
		"transitions" => array("nextPlayer" => 25)
	),
	25 => array(
		"name" => "country",
		"description" => clienttranslate('Countries choice'),
		"type" => "game",
		"action" => "stCountries",
		"transitions" => ["countryChoice" => 20, "setup" => 50]
	),
	50 => array(
		"name" => "setup",
		"description" => clienttranslate('Game setup'),
		"type" => "game",
		"action" => "stSetup",
		"transitions" => ["startOfHuntingRound" => 100]
	),
	100 => array(
		"name" => "startOfHuntingRound",
		"description" => clienttranslate('Start of round'),
		"type" => "game",
		"action" => "stStartOfHuntingRound",
		"transitions" => ["playerHuntingRound" => 110]
	),
	110 => array(
		"name" => "playerHuntingRound",
		"description" => clienttranslate('${actplayer} must place any number of hunting team cards'),
		"descriptionmyturn" => clienttranslate('${you} must place any number of hunting team cards'),
		"type" => "activeplayer",
		'args' => 'argPlayerHuntingRound',
		"possibleactions" => array("hunt", "abandon"),
		"transitions" => array("nextPlayer" => 120, "gameEnd" => 99)
	),
	120 => array(
		"name" => "endOfHuntingRound",
		"updateGameProgression" => true,
		"description" => clienttranslate('End of round'),
		"type" => "game",
		"action" => "stEndOfHuntingRound",
		"transitions" => ["startOfHuntingRound" => 100, "startOfPurchaseRound" => 200]
	),
	200 => array(
		"name" => "startOfPurchaseRound",
		"description" => clienttranslate('Start of round'),
		"type" => "game",
		"action" => "stStartOfPurchaseRound",
		"transitions" => ["playerPurchaseRound" => 210, "nextPlayer" => 220]
	),
	210 => array(
		"name" => "playerPurchaseRound",
		"description" => clienttranslate('${actplayer} may purchase dogs'),
		"descriptionmyturn" => clienttranslate('${you} may purchase dogs'),
		"type" => "activeplayer",
		'args' => 'argPlayerPurchaseRound',
		"possibleactions" => array("purchase", "pass"),
		"transitions" => array("nextPlayer" => 220)
	),
	220 => array(
		"name" => "endOfPurchaseRound",
		"updateGameProgression" => true,
		"description" => clienttranslate('End of round'),
		"type" => "game",
		"action" => "stEndOfPurchaseRound",
		"transitions" => ["startOfPurchaseRound" => 200, "startOfHuntingRound" => 100]
	),
	99 => [
		"name" => "gameEnd",
		"description" => clienttranslate("End of game"),
		"type" => "manager",
		"action" => "stGameEnd",
		"args" => "argGameEnd"
	]
];
