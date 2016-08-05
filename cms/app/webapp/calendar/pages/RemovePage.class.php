<?php

class RemovePage extends WebPage{
	
	private $error;
	
	function doPost(){
		
		$startDate = $this->getScheduleArray($_POST["start"]);
		$endDate = $this->getScheduleArray($_POST["end"]);
		
		if(soy2_check_token()&&$endDate>=$startDate){
			
			$start = $_POST["start"];
			$end = $_POST["end"];
			
			$title = (isset($_POST["title"]) && (int)$_POST["title"] > 0) ? (int)$_POST["title"] : null;
			
			$count = $end["month"]-$start["month"]+1;
			$count = ($end["year"]==$start["year"])?$count:$count+12;
			
			$logic = SOY2Logic::createInstance("logic.RemoveLogic");
			$logic->removeSchedule($start,$end,$count,$endDate,$title);
			
			
			CMSApplication::jump();
			
		}
		
		$this->error = true;
	}
	
	function getScheduleArray($array){
		$year = $array["year"];
		$month = $array["month"];
		if(strlen($month)==1)$month = "0".$month;
		$day = $array["day"];
		if(strlen($day)==1)$day = "0".$day;
		
		return $year.$month.$day;
	}

    function __construct() {
    	
    	WebPage::WebPage();
    	
    	$this->createAdd("error","HTMLModel",array(
    		"visible" => $this->error == true
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("year","HTMLSelect",array(
    		"name" => "start[year]",
    		"options" => range(2010,date("Y")+1),
    		"selected" => date("Y")
    	));
    	$this->createAdd("month","HTMLSelect",array(
    		"name" => "start[month]",
    		"options" => range(1,12),
    		"selected" => date("n")
    	));
    	$this->createAdd("day","HTMLSelect",array(
    		"name" => "start[day]",
    		"options" => range(1,31),
    		"selected" => date("j")
    	));
    	
    	$this->createAdd("end_year","HTMLSelect",array(
    		"name" => "end[year]",
    		"options" => range(2010,date("Y")+1),
    		"selected" => date("Y")
    	));
    	$this->createAdd("end_month","HTMLSelect",array(
    		"name" => "end[month]",
    		"options" => range(1,12),
    		"selected" => date("n")
    	));
    	$this->createAdd("end_day","HTMLSelect",array(
    		"name" => "end[day]",
    		"options" => range(1,31),
    		"selected" => date("j")
    	));
    	
    	$this->createAdd("title","HTMLSelect",array(
    		"name" => "title",
    		"options" => $this->getTitleArray(),
    	));
    }
    
    function getTitleArray(){
		$dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
		$titles = $dao->get();
		
		$array = array();
		foreach($titles as $title){
			$array[$title->getId()] = $title->getTitle();
		}
		return $array;
	}
}
?>