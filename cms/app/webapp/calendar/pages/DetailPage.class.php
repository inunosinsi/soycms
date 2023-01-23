<?php

class DetailPage extends WebPage{

	private $error=false;
	private $id;
	private $dao;

	private $repeatArray = array(
								"1"	=> "毎日",
								"2"	=> "毎週",
								"3" => "毎月"
							);

	function doPost(){

		if(soy2_check_token()){
			$item = $_POST["item"];

			if(isset($_POST["confirm"]) && self::_check($item)){

				$dao = self::_dao();
				$item["scheduleDate"] = soycalendar_get_timestamp($item["year"], $item["month"], $item["day"]);
				$item = SOY2::cast("SOYCalendar_Item", $item);
				
				try{
					$dao->update($item);
					CMSApplication::jump();
				}catch(Exception $e){
					var_dump($e);
				}
			}

			if(isset($_POST["delete"])){
				try{
					self::_dao()->deleteById($item["id"]);
					CMSApplication::jump();
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}
		$this->error = true;
	}

	private function _check(array $item){
		return (strlen($item["start"]) > 0 && strlen($item["end"]) > 0);
	}

    function __construct($args) {
    	$this->id = (isset($args[0]) && is_numeric($args[0])) ? (int)$args[0] : 0;

    	parent::__construct();

		try{
    		$item = self::_dao()->getById($this->id);
    	}catch(Exception $e){
    		$item = new SOYCalendar_Item();
    	}

    	$ynjw = soycalendar_get_Ynjw($item->getScheduleDate());

    	$this->addLabel("date", array(
    		"text" => $ynjw["year"]."年".$ynjw["month"]."月".$ynjw["day"]."日"
    	));

		DisplayPlugin::toggle("error", $this->error);

    	$this->addForm("form");

    	$this->addSelect("title", array(
    		"name" => "item[title]",
    		"options" => CalendarAppUtil::getTitleList(),
    		"selected" => $item->getTitleId()
    	));

    	$this->addSelect("year", array(
    		"name" => "item[year]",
    		"options" => CalendarAppUtil::getYearArray(),
    		"selected" => $ynjw["year"]
    	));
    	$this->addSelect("month", array(
    		"name" => "item[month]",
    		"options" => range(1,12),
    		"selected" => $ynjw["month"]
    	));
    	$this->addSelect("day", array(
    		"name" => "item[day]",
    		"options" => range(1,31),
    		"selected" => $ynjw["day"]
    	));
    	$this->addLabel("start", array(
    		"name" => "item[start]",
    		"value" => $item->getStart(),
    		"style" => "width:20%;"
    	));
    	$this->addLabel("end", array(
    		"name" => "item[end]",
    		"value" => $item->getEnd(),
    		"style" => "width:20%;"
    	));
    	$this->addLabel("id", array(
    		"name" => "item[id]",
    		"value" => $item->getId()
    	));
    	$this->addLabel("create_date", array(
    		"name" => "item[createDate]",
    		"value" => $item->getCreateDate()
    	));

    	$this->addLabel("calendar", array(
    		"html" => SOY2Logic::createInstance("logic.CalendarLogic")->getCalendar($ynjw["year"],$ynjw["month"],true)
    	));
    }

	private function _dao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		return $d;
	}
}
