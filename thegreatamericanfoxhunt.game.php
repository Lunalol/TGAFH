<?php
require_once( APP_GAMEMODULE_PATH . 'module/table/table.game.php' );
require_once('modules/constants.php');
require_once('modules/gameStates.php');
require_once('modules/gameStateArguments.php');
require_once('modules/gameStateActions.php');

class TheGreatAmericanFoxHunt extends Table
{
	use gameStates;
	use gameStateArguments;
	use gameStateActions;
//
	function __construct()
	{
		parent::__construct();
//
// Initialize globals
//
		self::initGameStateLabels(['countries' => COUNTRIES, 'firstPlayer' => 10]);
//
// Initialize hunter deck
//
		$this->hunters = self::getNew("module.common.deck");
		$this->hunters->init("hunters");
//
// Initialize animal deck
//
		$this->animals = self::getNew("module.common.deck");
		$this->animals->init("animals");
	}
	protected function getGameName(): string
	{
		return "thegreatamericanfoxhunt";
	}
	protected function setupNewGame($players, $options = [])
	{
		$gameinfos = self::getGameinfos();
		if (self::getGameStateValue('countries') == 1) $default_colors = array_fill(0, sizeof($players), 'ffffffff');
		else $default_colors = $gameinfos['player_colors'];
//
		$values = [];
		foreach ($players as $player_id => $player)
		{
			$color = array_shift($default_colors);
			$values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
		}
		self::DbQuery("INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES " . implode($values, ','));
//
		if (self::getGameStateValue('countries') == 0) self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
		self::reloadPlayersBasicInfos();
//
// Init game statistics
//
		self::initStatistics();
//
// Activate first player
//
		self::setGameStateValue('firstPlayer', $this->activeNextPlayer());
	}
	protected function initStatistics()
	{
		$this->initStat('table', 'rounds', 1);
		$this->initStat('player', 'rounds', 1);
		foreach ($this->PREY as $animal) $this->initStat('player', $animal, 0);
		foreach ($this->PREY as $animal) $this->initStat('player', $animal . '_sold', 0);
		$this->initStat('player', 'dogs', 0);
	}
	protected function getAllDatas(): array
	{
		$result = [];
//
		$result['players'] = self::getCollectionFromDb("SELECT player_id id, player_score score, country FROM player");
		foreach (array_keys($result['players']) as $player_id) $result['players'][$player_id]['counts'] = self::counts($player_id);
//
		$result['ANIMALS'] = $this->ANIMALS;
		$result['DOGS'] = $this->DOGS;
		$result['animals'] = $this->animals->countCardsInLocations();
		$result['hands'] = $this->hunters->countCardsByLocationArgs('hand');
		$result['discards'] = $this->hunters->countCardsByLocationArgs('discard');
		$result['dogs'] = $this->hunters->countCardsByLocationArgs(0);
//
		$result['preys'] = [];
		foreach ($this->animals->getCardsInLocation('prey') as $animal)
		{
			$result['preys'][$animal['id']] = $animal;
			$result['preys'][$animal['id']]['hunters'] = $this->hunters->getCardsInLocation($animal['id']);
		}
//
		return $result;
	}
	function counts(int $player_id): array
	{
		$counts = [];
//
		$foxes = $this->animals->getCardsOfTypeInLocation(FOXES, null, 'killed', $player_id);
		$counts[$this->PREY[FOXES]][0] = sizeof($foxes);
		$counts[$this->PREY[FOXES]][1] = '?';
		$counts[$this->PREY[FOXES]][2] = $foxes;
//
		foreach ($this->VICTORY as $animal => $count)
		{
			$killed = $this->animals->getCardsOfTypeInLocation($animal, null, 'killed', $player_id);
			$counts[$this->PREY[$animal]][0] = sizeof($killed);
			$counts[$this->PREY[$animal]][1] = $count;
			$counts[$this->PREY[$animal]][2] = $killed;
		}
//
		$score = 0;
		foreach ($counts as $prey => $count) if ($prey !== 'Fox') $score = max($score, round(($count[0] + $counts['Fox'][0]) / $count[1] * 100));
		self::dbSetScore($player_id, $score, $this->animals->countCardsInLocation('killed', $player_id));
//
		return $counts;
	}
	function coloredCountryName(string $country): string
	{
		return '<span style="background-color:#' . $this->COLORS[$country] . ';font-weight:bold;">${country}</span>';
	}
	function dbSetScore(int $player_id, int $score, int $score_aux = 0): void
	{
		$this->DbQuery("UPDATE player SET player_score=$score, player_score_aux=$score_aux WHERE player_id = $player_id");
		$this->notifyAllPlayers('update_score', '', ['player_id' => $player_id, 'score' => $score]);
	}
	function getGameProgression(): int
	{
		return $this->getUniqueValueFromDB("SELECT MAX(player_score) FROM player");
	}
	function zombieTurn($state, $active_player): void
	{
		if ($state['type'] === "activeplayer")
		{
			switch ($state['name'])
			{
				default:
					$this->
					gamestate->nextState("zombiePass");
					break;
			}
			return;
		}
		if ($state['type'] === "multipleactiveplayer")
		{
			$this->gamestate->setPlayerNonMultiactive($active_player, '');
			return;
		}
		throw new feException("Zombie mode not supported at this game state: " . $state['name']);
	}
	function upgradeTableDb($from_version): void
	{

	}
}
