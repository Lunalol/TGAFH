<?php
//
// Countries by name
//
$this->COUNTRIES = [
	1 => clienttranslate('United States'),
	2 => clienttranslate('Great Britain'),
	3 => clienttranslate('Japan'),
	4 => clienttranslate('Ghana'),
	5 => clienttranslate('Sweden'),
	6 => clienttranslate('Spain'),
];
$this->COLORS = [
	1 => "5663a7",
	2 => "c04746",
	3 => "a8b23e",
	4 => "aea296",
	5 => "f0de41",
	6 => "d150d3"];
//
$this->HUNTERS = [
	['type' => 0, 'type_arg' => 2, 'nbr' => 1],
	['type' => 0, 'type_arg' => 3, 'nbr' => 1],
	['type' => 0, 'type_arg' => 4, 'nbr' => 1],
	['type' => 0, 'type_arg' => 5, 'nbr' => 1],
	['type' => 0, 'type_arg' => 6, 'nbr' => 1],
];
$this->DOGS = [
	['type' => DOGS, 'type_arg' => 2, 'nbr' => 5],
	['type' => DOGS, 'type_arg' => 3, 'nbr' => 4],
	['type' => DOGS, 'type_arg' => 4, 'nbr' => 3],
];
//
// Animals by name
//
$this->PREY = [
	SQUIRRELS => clienttranslate('Squirrel'),
	HARES => clienttranslate('Hare'),
	DEERS => clienttranslate('Deer'),
	BEARS => clienttranslate('Bear'),
	FOXES => clienttranslate('Fox'),
];
//
// Animals by name
//
$this->PREYS = [
	SQUIRRELS => clienttranslate('Squirrels'),
	HARES => clienttranslate('Hares'),
	DEERS => clienttranslate('Deers'),
	BEARS => clienttranslate('Bears'),
	FOXES => clienttranslate('Foxes'),
];
//
// Cards by animal/value/count
//
$this->ANIMALS = [
	['type' => SQUIRRELS, 'type_arg' => 1, 'nbr' => 11],
	['type' => SQUIRRELS, 'type_arg' => 2, 'nbr' => 10],
	['type' => SQUIRRELS, 'type_arg' => 3, 'nbr' => 9],
	['type' => HARES, 'type_arg' => 3, 'nbr' => 8],
	['type' => HARES, 'type_arg' => 4, 'nbr' => 7],
	['type' => HARES, 'type_arg' => 5, 'nbr' => 6],
	['type' => HARES, 'type_arg' => 6, 'nbr' => 5],
	['type' => DEERS, 'type_arg' => 6, 'nbr' => 6],
	['type' => DEERS, 'type_arg' => 7, 'nbr' => 5],
	['type' => DEERS, 'type_arg' => 8, 'nbr' => 4],
	['type' => DEERS, 'type_arg' => 9, 'nbr' => 3],
	['type' => BEARS, 'type_arg' => 9, 'nbr' => 4],
	['type' => BEARS, 'type_arg' => 10, 'nbr' => 4],
	['type' => BEARS, 'type_arg' => 11, 'nbr' => 3],
	['type' => BEARS, 'type_arg' => 12, 'nbr' => 2],
	['type' => FOXES, 'type_arg' => 10, 'nbr' => 8],
];
//
// Victory conditions by animal
//
$this->VICTORY = [
	SQUIRRELS => 6,
	HARES => 5,
	DEERS => 4,
	BEARS => 3,
];
