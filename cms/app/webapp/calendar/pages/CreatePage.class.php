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

//			if(self::check($item)){
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

					 $flag = ($repeat == 1) ? self::getDayFlag() : $_POST["repeat"]["day"];

					 $logic->insertSchedule($item,$end,$endDate,$count,$flag);
					 CMSApplication::jump();
				}

//			}

		}

		$this->error = true;
	}

	private function check($item){
		return (strlen($item["start"])>0&&strlen($item["end"])>0);
	}

	function getScheduleArray($array){
		$year = $array["year"];
		$month = $array["month"];
		if(strlen($month)==1)$month = "0".$month;
		$day = $array["day"];
		if(strlen($day)==1)$day = "0".$day;

		return $year.$month.$day;
	}

	private function getDayFlag(){
		return array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
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

    	parent::__construct();

    	if($this->error == true){
    		$year = $_POST["item"]["year"];
    		$month = $_POST["item"]["month"];
    		$day = $_POST["item"]["day"];
    	}

		DisplayPlugin::toggle("error", $this->error === true);

    	$this->addForm("form");

    	$this->createAdd("title","HTMLSelect",array(
    		"name" => "item[title]",
    		"options" => CalendarAppUtil::getTitleList(),
    		"selected" => (isset($_POST["item"]["title"])) ? $_POST["item"]["title"] : ""
    	));

    	$this->createAdd("year","HTMLSelect",array(
    		"name" => "item[year]",
    		"options" => CalendarAppUtil::getYearArray(),
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
    		"value" => (isset($_POST["item"]["start"])) ? $_POST["item"]["start"] : "",
    		"style" => "width:15%;"
    	));
    	$this->createAdd("end","HTMLInput",array(
    		"name" => "item[end]",
    		"value" => (isset($_POST["item"]["end"])) ? $_POST["item"]["end"] : "",
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
    		"selected" => (isset($_POST["repeat"]["type"])) ? $_POST["repeat"]["type"] : ""
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
    		"options" => CalendarAppUtil::getYearArray(),
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

    	self::buildCheckboxList();

    	$this->addLabel("calendar", array(
    		"html" => SOY2Logic::createInstance("logic.CalendarLogic")->getCurrentCalendar(true)
    	));
    }

    private function buildCheckboxList(){
		foreach(self::getDayFlag() as $flg){
			$lowFlg = strtolower($flg);
			$this->addCheckBox($lowFlg, array(
	    		"name" => "repeat[day][]",
	    		"value" => $flg,
	    		"elementId" => $lowFlg,
	    		"selected" => (isset($_POST["repeat"]["day"]) && in_array($flg, $_POST["repeat"]["day"]))
	    	));
		}
    }
}
