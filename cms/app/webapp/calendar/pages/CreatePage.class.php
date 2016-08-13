<?php

class CreatePage extends WebPage{
	
	private $error=false;
	private $logic;
	private $repeatArray = array("1"=>"毎日","2"=>"毎週","3"=>"毎月");
	
	function doPost(){
	
		if(soy2_check_token()){
			
			//始めに終了日の値に誤りがないかチェック
			$startDate = $this->getScheduleArray($_POST["item"]);
			$endDate = $this->getScheduleArray($_POST["end"]);
			
			if(!$this->logic)$this->logic = SOY2Logic::createInstance("logic.InsertLogic");
			$logic = $this->logic;
			
			$item = $_POST["item"];
			
//			if($this->check($item)){
				if($endDate < $startDate||!isset($_POST["repeat"]["confirm"])){
					//当日のデータのみインサート
					$lastDate = $logic->getLastDate($item["month"],$item["year"]);			
					if($startDate <= $lastDate){
						$logic->insertComplete($item,$startDate);
						CMSApplication::jump();
					}
					
	
				//繰返し登録を利用する
				}else{
					 
					 $repeat = $_POST["repeat"]["type"];
					 
					 $end = $_POST["end"];
					 
					 switch($repeat){
					 	case 3:
					 		$count = $end["month"]-$item["month"]+1;
							$count = ($end["year"]==$item["year"])?$count:$count+12;
							break;
						default:
							$count = 1;
					 }
					 
					 if($repeat == 1){
					 	$flag = $this->getDayFlag();
					 }else{
					 	$flag = $_POST["repeat"]["day"];
					 }
					 
					 $logic->insertSchedule($item,$end,$endDate,$count,$flag);
					 CMSApplication::jump();
				}
				
//			}
			
		}
		
		$this->error = true;
	}
	
	function check($item){
		return (strlen($item["start"])>0&&strlen($item["end"])>0)?true:false;
	}
	
	function getScheduleArray($array){
		$year = $array["year"];
		$month = $array["month"];
		if(strlen($month)==1)$month = "0".$month;
		$day = $array["day"];
		if(strlen($day)==1)$day = "0".$day;
		
		return $year.$month.$day;
	}
	
	function getDayFlag(){
		return array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
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
	
    function __construct() {
    	
    	$year = date("Y",time());
    	$month = date("n",time());
    	$day = date("j",time());
    	
    	if(isset($_GET["year"])){
    		$year = $_GET["year"];
    		$month = $_GET["month"];
    		$day = $_GET["day"];
    	}
    	
    	WebPage::__construct();
    	
    	if($this->error == true){
    		$year = $_POST["item"]["year"];
    		$month = $_POST["item"]["month"];
    		$day = $_POST["item"]["day"];
    	}
    	
    	$this->createAdd("error","HTMLModel",array(
    		"visible" => $this->error == true
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	    	
    	$this->createAdd("title","HTMLSelect",array(
    		"name" => "item[title]",
    		"options" => $this->getTitleArray(),
    		"selected" => @$_POST["item"]["title"]
    	));
    	
    	$this->createAdd("year","HTMLSelect",array(
    		"name" => "item[year]",
    		"options" => $this->getYearArray(),
    		"selected" => $year
    	));
    	$this->createAdd("month","HTMLSelect",array(
    		"name" => "item[month]",
    		"options" => range(1,12),
    		"selected" => $month
    	));
    	$this->createAdd("day","HTMLSelect",array(
    		"name" => "item[day]",
    		"options" => range(1,31),
    		"selected" => $day
    	));
    	$this->createAdd("start","HTMLInput",array(
    		"name" => "item[start]",
    		"value" => @$_POST["item"]["start"],
    		"style" => "width:15%;"
    	));
    	$this->createAdd("end","HTMLInput",array(
    		"name" => "item[end]",
    		"value" => @$_POST["item"]["end"],
    		"style" => "width:15%;"
    	));
    	
    	$this->createAdd("confirm","HTMLCheckbox",array(
    		"name" => "repeat[confirm]",
    		"value" => 1,
    		"elementId" => "confirm",
    		"selected" => false
    	));
    	
    	$this->createAdd("repeat","HTMLSelect",array(
    		"name" => "repeat[type]",
    		"options" => $this->repeatArray,
    		"selected" => @$_POST["repeat"]["type"]
    	));
    	
    	if(isset($_GET["year"])){
    		$end = array("year"=>$_GET["year"],"month"=>$_GET["month"],"day"=>$_GET["day"]);
    	}else{
    		$end = array("year"=>date("Y",time()),"month"=>date("n",time()),"day"=>date("j",time()));
    	}
    	
    	if(isset($_POST["end"])){	
    		$end = array("year"=>$_POST["end"]["year"],"month"=>$_POST["end"]["month"],"day"=>$_POST["end"]["day"]);
    	}
    	
    	$this->createAdd("end_year","HTMLSelect",array(
    		"name" => "end[year]",
    		"options" => $this->getYearArray(),
    		"selected" => $end["year"]
    	));
    	$this->createAdd("end_month","HTMLSelect",array(
    		"name" => "end[month]",
    		"options" => range(1,12),
    		"selected" => $end["month"]
    	));
	   	$this->createAdd("end_day","HTMLSelect",array(
    		"name" => "end[day]",
    		"options" => range(1,31),
    		"selected" => $end["day"]
    	));
    	
    	$this->getCheckboxList();
    	
    	$logic = SOY2Logic::createInstance("logic.CalendarLogic");
    	
    	$this->createAdd("calendar","HTMLLabel",array(
    		"html" => $logic->getCurrentCalendar(true)
    	));
    }
    
    function getCheckboxList(){
    	
    	$this->createAdd("sun","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Sun",
    		"elementId" => "sun",
    		"selected" => @in_array("Sun",$_POST["repeat"]["day"])
    	));
    	
    	$this->createAdd("mon","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Mon",
    		"elementId" => "mon",
    		"selected" => @in_array("Mon",$_POST["repeat"]["day"])
    	));
    	
    	$this->createAdd("tue","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Tue",
    		"elementId" => "tue",
    		"selected" => @in_array("Tue",$_POST["repeat"]["day"])
    	));
    	
    	$this->createAdd("wed","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Wed",
    		"elementId" => "wed",
    		"selected" => @in_array("Wed",$_POST["repeat"]["day"])
    	));
    	
    	$this->createAdd("thu","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Thu",
    		"elementId" => "thu",
    		"selected" => @in_array("Thu",$_POST["repeat"]["day"])
    	));
    	
    	$this->createAdd("fri","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Fri",
    		"elementId" => "fri",
    		"selected" => @in_array("Fri",$_POST["repeat"]["day"])
    	));
    	
    	$this->createAdd("sat","HTMLCheckBox",array(
    		"name" => "repeat[day][]",
    		"value" => "Sat",
    		"elementId" => "sat",
    		"selected" => @in_array("Sat",$_POST["repeat"]["day"])
    	));
    	
    }
    
    function getYearArray(){
    	$year = date("Y",time());
    	
    	$array = array();
    	$array[] = $year;
    	$array[] = $year+1;
    	
    	return $array;
    } 
}
?>