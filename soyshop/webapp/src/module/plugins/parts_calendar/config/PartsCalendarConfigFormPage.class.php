<?php

class PartsCalendarConfigFormPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::imports("module.plugins.parts_calendar.common.*");
	}

	function doPost(){
		if(!soy2_check_token()){
			$this->config->redirect();
		}

		//毎週
		$week = (isset($_POST["week"])) ? array_keys($_POST["week"]) : array();
		PartsCalendarCommon::saveConfig("week", $week);
		
		//曜日
		$dow = array();
		if(isset($_POST["dow"])){
			$post = $_POST["dow"];
			foreach($post as $key => $val){
				$dow[$key] = array_keys($post[$key]);
			}
		}
		PartsCalendarCommon::saveConfig("day_of_week", $dow);
		PartsCalendarCommon::saveConfig("md", PartsCalendarCommon::md($_POST["md"]));	//月日
		PartsCalendarCommon::saveConfig("ymd", PartsCalendarCommon::ymd($_POST["ymd"]));	//年月日
		PartsCalendarCommon::saveConfig("business_day", PartsCalendarCommon::ymd($_POST["business_day"]));	//営業日
		PartsCalendarCommon::saveConfig("other_day", PartsCalendarCommon::ymd($_POST["other_day"]));	//その他

		$soyCalConf = (isset($_POST["soycalendar"])) ? $_POST["soycalendar"] : array();
		PartsCalendarCommon::saveConfig("soycalendar_connect", $soyCalConf);	// SOY Calendar連携

		//キャッシュファイルの削除
		SOYShopCacheUtil::clearCache();

		$this->config->redirect("updated");
	}

	function execute(){

		parent::__construct();

		$this->addForm("calendar_form");

		//毎週
		$this->createAdd("week_holiday_list","WeekHolidayListComponent", array(
			"list" => PartsCalendarCommon::getWeek(),
			"config" => PartsCalendarCommon::getWeekConfig()
		));

		//第1週から第5週の各曜日
		$this->createAdd("day_of_week_list","DayOfWeekHolidayListComponent", array(
			"list" => PartsCalendarCommon::getWeek(),
			"config" => PartsCalendarCommon::getDayOfWeekConfig()
		));

		//月日
		$this->addTextArea("md", array(
			"text" => PartsCalendarCommon::getMdConfig(true),
			"name" => "md"
		));

		//年月日
		$this->addTextArea("ymd", array(
			"text" => PartsCalendarCommon::getYmdConfig(true),
			"name" => "ymd"
		));

		//営業日
		$this->addTextArea("business_day", array(
			"text" => PartsCalendarCommon::getBDConfig(true),
			"name" => "business_day"
		));

		//営業日
		$this->addTextArea("other_day", array(
			"text" => PartsCalendarCommon::getOtherConfig(true),
			"name" => "other_day"
		));

		// SOY Calendarと連携する
		self::_buildSOYCalendarConfigForm();

		// 設定例
		self::_buildConfigExampleArea();
	}

	private function _buildSOYCalendarConfigForm(){
		$isSoyCalendar = SOYAppUtil::checkInstalledApp("calendar");
		DisplayPlugin::toggle("soycalendar_connect", $isSoyCalendar);

		$titleList = ($isSoyCalendar) ? SOY2Logic::createInstance("module.plugins.parts_calendar.logic.SOYCalendarConnectLogic")->getTitleList() : array();

		$cnf = PartsCalendarCommon::getSOYCalendarConnectConfig();
		
		$this->addSelect("holiday_connect", array(
			"name" => "soycalendar[holiday]",
			"options" => $titleList,
			"selected" => (isset($cnf["holiday"]) && is_numeric($cnf["holiday"])) ? (int)$cnf["holiday"] : ""
		));

		$this->addSelect("business_day_connect", array(
			"name" => "soycalendar[business]",
			"options" => $titleList,
			"selected" => (isset($cnf["business"]) && is_numeric($cnf["business"])) ? (int)$cnf["business"] : ""
		));

		$this->addInput("auto_delete_month", array(
			"name" => "soycalendar[auto_delete]",
			"value" => (isset($cnf["auto_delete"])) ? (int)$cnf["auto_delete"] : 0
		));
	}

	private function _buildConfigExampleArea(){
		$displayLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.DisplayLogic");

		$this->addLabel("current_calendar", array(
			"html" => $displayLogic->getCurrentCalendar()
		));

		$this->addLabel("next_calendar", array(
			"html" => $displayLogic->getNextCalendar()
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
