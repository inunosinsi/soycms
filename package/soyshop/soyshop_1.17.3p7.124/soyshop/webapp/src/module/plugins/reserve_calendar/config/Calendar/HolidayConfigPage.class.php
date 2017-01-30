<?php

class HolidayConfigPage extends WebPage{

	private $config;
	private $itemId;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::imports("module.plugins.reserve_calendar.component.list.*");
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	function doPost(){
		if(!soy2_check_token()){
			$this->config->redirect();
		}
		//毎週
		if(isset($_POST["week"])){
			$post = array_keys($_POST["week"]);
		}else{
			$post = array();
		}
		ReserveCalendarUtil::saveWeekConfig($this->itemId, $post);

		//曜日
		if(isset($_POST["dow"])){
			$post = $_POST["dow"];
			$dow = array();
			foreach($post as $key => $val){
				$dow[$key] = array_keys($post[$key]);
			}
		}else{
			$dow = array();
		}
		ReserveCalendarUtil::saveDayOfWeekConfig($this->itemId, $dow);

		//月日
		if(isset($_POST["md"])){
			ReserveCalendarUtil::saveMdConfig($this->itemId, self::convertMd($_POST["md"]));
		}

		//年月日
		if(isset($_POST["ymd"])){
			ReserveCalendarUtil::saveYmdConfig($this->itemId, self::convertYmd($_POST["ymd"]));
		}

		//営業日
		if(isset($_POST["business_day"])){
			ReserveCalendarUtil::saveBDConfig($this->itemId, self::convertYmd($_POST["business_day"]));
		}

		//その他
		if(isset($_POST["other_day"])){
			ReserveCalendarUtil::saveOtherConfig($this->itemId, self::convertYmd($_POST["other_day"]));
		}

		$this->config->redirect("updated&holiday&item_id=" . $this->itemId);
	}

	function execute(){

		WebPage::__construct();
		
		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $this->itemId),
			"text" => self::getItemById($this->itemId)->getName() . "の詳細ページに戻る"
		));

		$this->addForm("form");

		//毎週
		$this->createAdd("week_holiday_list","WeekHolidayListComponent", array(
			"list" => ReserveCalendarUtil::getWeek(),
			"config" => ReserveCalendarUtil::getWeekConfig($this->itemId)
		));

		//第1週から第5週の各曜日
		$this->createAdd("day_of_week_list","DayOfWeekHolidayListComponent", array(
			"list" => ReserveCalendarUtil::getWeek(),
			"config" => ReserveCalendarUtil::getDayOfWeekConfig($this->itemId)
		));

		//月日
		$this->addTextArea("md", array(
			"text" => ReserveCalendarUtil::getMdConfig($this->itemId, true),
			"name" => "md"
		));

		//年月日
		$this->addTextArea("ymd", array(
			"text" => ReserveCalendarUtil::getYmdConfig($this->itemId, true),
			"name" => "ymd"
		));

		//営業日
		$this->addTextArea("business_day", array(
			"text" => ReserveCalendarUtil::getBDConfig($this->itemId, true),
			"name" => "business_day"
		));

		//営業日
		$this->addTextArea("other_day", array(
			"text" => ReserveCalendarUtil::getOtherConfig($this->itemId, true),
			"name" => "other_day"
		));

//		//カレンダー
//		$displayLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.DisplayLogic");
//
//		$this->addLabel("current_calendar", array(
//			"html" => $displayLogic->getCurrentCalendar()
//		));
//
//		$this->addLabel("next_calendar", array(
//			"html" => $displayLogic->getNextCalendar()
//		));
	}
	
	private function getItemById($itemId){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	/**
	 * convert Ymd
	 */
	private function convertYmd($date){
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
	private function convertMd($date){
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
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
?>