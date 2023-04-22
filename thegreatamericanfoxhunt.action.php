<?php

class action_thegreatamericanfoxhunt extends APP_GameAction
{
	// Constructor: please do not modify
	public function __default()
	{
		if (self::isArg('notifwindow'))
		{
			$this->view = "common_notifwindow";
			$this->viewArgs['table'] = self::getArg("table", AT_posint, true);
		} else
		{
			$this->view = "thegreatamericanfoxhunt_thegreatamericanfoxhunt";
			self::trace("Complete reinitialization of board game");
		}
	}
	public function country()
	{
		self::setAjaxMode();
//
		$country = self::getArg("country", AT_int, true);
		$this->game->acCountry($country);
//
		self::ajaxResponse("");
	}
	public function hunt()
	{
		self::setAjaxMode();
//
		$animal = self::getArg("animal", AT_int, true);
		$hunters_raw = self::getArg("hunters", AT_numberlist, true);
		if (substr($hunters_raw, -1) == ';') $hunters_raw = substr($hunters_raw, 0, -1);
		if ($hunters_raw == '') $hunters = [];
		else $hunters = explode(';', $hunters_raw);
//
		$this->game->acHunt($animal, $hunters);
//
		self::ajaxResponse("");
	}
	public function abandon()
	{
		self::setAjaxMode();
//
		$this->game->acAbandon();
//
		self::ajaxResponse("");
	}
	public function purchase()
	{
		self::setAjaxMode();
//
		$animals_raw = self::getArg("animals", AT_numberlist, true);
		if (substr($animals_raw, -1) == ';') $animals_raw = substr($animals_raw, 0, -1);
		if ($animals_raw == '') $animals = [];
		else $animals = explode(';', $animals_raw);
//
		$this->game->acPurchase($animals);
//
		self::ajaxResponse("");
	}
}
