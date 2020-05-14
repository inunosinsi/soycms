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
		if(isset($_POST["week"])){
			$post = $_POST["week"];
			$post = array_keys($post);

			SOYShop_DataSets::put("calendar.config.week", $post);
		}else{
			SOYShop_DataSets::put("calendar.config.week", array());
		}

		//曜日
		if(isset($_POST["dow"])){
			$post = $_POST["dow"];
			$dow = array();
			foreach($post as $key => $val){
				$dow[$key] = array_keys($post[$key]);
			}

			SOYShop_DataSets::put("calendar.config.day_of_week", $dow);
		}else{
			SOYShop_DataSets::put("calendar.config.day_of_week", array());
		}

		//月日
		if(isset($_POST["md"])){
			$config = $_POST["md"];
			SOYShop_DataSets::put("calendar.config.md", $this->convertMd($config));
		}

		//年月日
		if(isset($_POST["ymd"])){
			$config = $_POST["ymd"];
			SOYShop_DataSets::put("calendar.config.ymd", $this->convertYmd($config));
		}

		//営業日
		if(isset($_POST["business_day"])){
			$config = $_POST["business_day"];

			SOYShop_DataSets::put("calendar.config.business_day", $this->convertYmd($config));
		}

		//その他
		if(isset($_POST["other_day"])){
			$config = $_POST["other_day"];

			SOYShop_DataSets::put("calendar.config.other_day", $this->convertYmd($config));
		}

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

		//カレンダー
		$displayLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.DisplayLogic");

		$this->addLabel("current_calendar", array(
			"html" => $displayLogic->getCurrentCalendar()
		));

		$this->addLabel("next_calendar", array(
			"html" => $displayLogic->getNextCalendar()
		));
	}

	/**
	 * convert Ymd
	 */
	function convertYmd($date){
		$array = explode("\n", $date);

		$val = array();
		foreach($array as $line){
			$line = mb_convert_kana(trim($line), "a");
			if(preg_match("|^\d{4}\/\d{2}\/\d{2}$|", $line) || preg_match("|^\d{4}-\d{2}-\d{2}$|", $line)){
				$line = str_replace("-", "/", $line);
				$val[] = $line;
			}
		}
		return $val;
	}

	/**
	 * convert md
	 */
	function convertMd($date){
		$array = explode("\n", $date);

		$val = array();
		foreach($array as $line){
			$line = mb_convert_kana(trim($line), "a");
			if(preg_match("|^\d{2}\/\d{2}$|", $line) || preg_match("|^\d{2}-\d{2}$|", $line)){
				$line = str_replace("-", "/", $line);
				$val[] = $line;
			}
		}
		return $val;
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>
