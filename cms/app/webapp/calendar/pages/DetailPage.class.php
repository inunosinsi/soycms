<?php

class DetailPage extends WebPage{

	private $error;
	private $id;
	private $dao;

	private $titleArray = array(
    							"1" => "午前",
    							"2" => "午後",
    							"3" => "夜間",
							);
	private $repeatArray = array(
								"1"	=> "毎日",
								"2"	=> "毎週",
								"3" => "毎月"
							);

	function doPost(){

		if(soy2_check_token()){

			$item = $_POST["item"];

			if(isset($_POST["confirm"])/**&&$this->check($item)==true**/){

				$item["schedule"] = $this->getSchedule($item);
				$item = SOY2::cast("SOYCalendar_Item",$item);
				$item->setUpdateDate(time());

				try{
					$this->dao->update($item);
					CMSApplication::jump();
				}catch(Exception $e){
					var_dump($e);
				}
			}

			if(isset($_POST["delete"])){
				try{
					$this->dao->deleteById($item["id"]);
					CMSApplication::jump();
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}
		$this->error = true;
	}

	function check($item){
		if(strlen($item["start"])>0&&strlen($item["end"])>0){
			return true;
		}else{
			return false;
		}
	}

	function getSchedule($array){
		$year = $array["year"];
		$month = $array["month"];
		if(strlen($month)==1)$month = "0".$month;
		$day = $array["day"];
		if(strlen($day)==1)$day = "0".$day;

		return $year.$month.$day;
	}

    function __construct($args) {

    	$id = $args[0];
    	$this->id = $id;

    	$this->dao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
    	try{
    		$item = $this->dao->getById($id);
    	}catch(Exception $e){
    		$item = new SOYCalendar_Item();
    	}

    	$schedule = $this->getDateArray($item->getSchedule());

    	parent::__construct();

    	$this->createAdd("date","HTMLLabel",array(
    		"text" => $schedule["year"]."年".$schedule["month"]."月".$schedule["day"]."日"
    	));

		DisplayPlugin::toggle("error", $this->error === true);

    	$this->createAdd("form","HTMLForm");

    	$this->createAdd("title","HTMLSelect",array(
    		"name" => "item[title]",
    		"options" => CalendarAppUtil::getTitleList(),
    		"selected" => $item->getTitle()
    	));

    	$this->createAdd("year","HTMLSelect",array(
    		"name" => "item[year]",
    		"options" => CalendarAppUtil::getYearArray(),
    		"selected" => $schedule["year"]
    	));
    	$this->createAdd("month","HTMLSelect",array(
    		"name" => "item[month]",
    		"options" => range(1,12),
    		"selected" => $schedule["month"]
    	));
    	$this->createAdd("day","HTMLSelect",array(
    		"name" => "item[day]",
    		"options" => range(1,31),
    		"selected" => $schedule["day"]
    	));
    	$this->createAdd("start","HTMLLabel",array(
    		"name" => "item[start]",
    		"value" => $item->getStart(),
    		"style" => "width:20%;"
    	));
    	$this->createAdd("end","HTMLLabel",array(
    		"name" => "item[end]",
    		"value" => $item->getEnd(),
    		"style" => "width:20%;"
    	));
    	$this->createAdd("id","HTMLLabel",array(
    		"name" => "item[id]",
    		"value" => $item->getId()
    	));
    	$this->createAdd("create_date","HTMLLabel",array(
    		"name" => "item[createDate]",
    		"value" => $item->getCreateDate()
    	));

    	$logic = SOY2Logic::createInstance("logic.CalendarLogic");

    	$this->createAdd("calendar","HTMLLabel",array(
    		"html" => $logic->getCalendar($schedule["year"],$schedule["month"],true)
    	));
    }

    function getDateArray($time){
    	$array = array();
    	$array["year"] = substr($time,0,4);
    	$array["month"] = substr($time,4,2);
    	$array["day"] = substr($time,6,2);

    	return $array;
    }
}
