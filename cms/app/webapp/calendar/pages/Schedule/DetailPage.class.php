<?php

class DetailPage extends WebPage{

	private $id;
	private $error=false;
	private $repeatArray = array("1"=>"毎日","2"=>"毎週","3"=>"毎月");

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["confirm"])){
				$item = $_POST["item"];
				$startDate = soycalendar_get_schedule_by_array($item);
				
				$logic = SOY2Logic::createInstance("logic.InsertLogic");

				//繰り返し登録	繰り返し登録は新規登録の時のみ
				//if(false){	//まずは一回のみの登録
				if($this->id === 0 && isset($_POST["repeat"]["confirm"])){
					$endDate = soycalendar_get_schedule_by_array($_POST["end"]);
					if($endDate > $startDate) {
						$end = $_POST["end"];
						
						switch($_POST["repeat"]["type"]){
							case 3:
								$cnt = $end["month"] - $item["month"] + 1;
								if($end["year"] != $item["year"]) $cnt = $cnt + 12;
								break;
							default:
								$cnt = 1;
						}

						$ws = ((int)$_POST["repeat"]["type"] === 1) ? self::_getDayFlag() : $_POST["repeat"]["day"];
						$logic->insertSchedules($item, $endDate, $cnt, $ws);
						CMSApplication::jump();
					}
				}

				/** 繰り返し登録を利用しない **/
				
				//当日のデータのみインサート
				if($this->id === 0){	 // 新規登録
					if($startDate <= soycalendar_get_last_date_timestamp($item["year"],$item["month"])){	// この行でありえない日付(2/31等)を除く
						$item["scheduleDate"] = $startDate;
						if($logic->save($item, $this->id)){
							CMSApplication::jump();
						}
						
					}
				}else{	//更新
					if($logic->save($item, $this->id)){
						CMSApplication::jump("Schedule.Detail." . $this->id);
					}
				}				
			}
			
			//予定の削除
			if(isset($_POST["delete"])){
				if(SOY2Logic::createInstance("logic.RemoveLogic")->remove($this->id)){
					CMSApplication::jump();
				}
			}
		}

		$this->error = true;
	}

	private function _getDayFlag(){
		return array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
	}

    function __construct($args) {
		$this->id = (isset($args[0])) ? (int)$args[0] : 0;

    	parent::__construct();

		DisplayPlugin::toggle("error", $this->error);
		DisplayPlugin::toggle("report_form_checkbox", $this->id === 0);
		DisplayPlugin::toggle("report_form_table", $this->id === 0);
		DisplayPlugin::toggle("register_button_area", $this->id === 0);
		DisplayPlugin::toggle("update_button_area", $this->id > 0);

		$itemObj = self::_getItemObject($this->id);
		
    	$this->addForm("form");

    	$this->addSelect("title", array(
    		"name" => "item[titleId]",
    		"options" => CalendarAppUtil::getTitleList(),
    		"selected" => $itemObj->getTitleId()
    	));

		
    	$this->addSelect("year", array(
    		"name" => "item[year]",
    		"options" => CalendarAppUtil::getYearArray(),
    		"selected" => date("Y", $itemObj->getScheduleDate())
    	));
    	$this->addSelect("month", array(
    		"name" => "item[month]",
    		"options" => range(1,12),
    		"selected" => date("n", $itemObj->getScheduleDate())
    	));
    	$this->addSelect("day", array(
    		"name" => "item[day]",
    		"options" => range(1,31),
    		"selected" => date("j", $itemObj->getScheduleDate())
    	));
    	$this->addInput("start", array(
    		"name" => "item[start]",
    		"value" => $itemObj->getStart(),
    		"style" => "width:15%;"
    	));
    	$this->addInput("end", array(
    		"name" => "item[end]",
    		"value" => $itemObj->getEnd(),
    		"style" => "width:15%;"
    	));

		/** カスタム項目 */
		$customs = CalendarAppUtil::getCustoms();
		DisplayPlugin::toggle("custom_items", count($customs));

		$this->createAdd("custom_item_checkbox_list", "_common.CustomItemCheckBoxListComponent", array(
			"list" => $customs,
			"checkedList" => ($this->id > 0) ? self::_getCustomItemCheckedList($this->id) : array()
		));

    	$this->addCheckBox("confirm", array(
    		"name" => "repeat[confirm]",
    		"value" => 1,
    		"elementId" => "confirm",
    		"selected" => false
    	));

    	$this->addSelect("repeat", array(
    		"name" => "repeat[type]",
    		"options" => $this->repeatArray,
    		"selected" => (isset($_POST["repeat"]["type"])) ? $_POST["repeat"]["type"] : ""
    	));

    	$end = self::_getYnjArrayOnEnd();

    	$this->addSelect("end_year", array(
    		"name" => "end[year]",
    		"options" => CalendarAppUtil::getYearArray(),
    		"selected" => $end["year"]
    	));
    	$this->addSelect("end_month", array(
    		"name" => "end[month]",
    		"options" => range(1,12),
    		"selected" => $end["month"]
    	));
	   	$this->addSelect("end_day", array(
    		"name" => "end[day]",
    		"options" => range(1,31),
    		"selected" => $end["day"]
    	));

    	self::_buildCheckboxList();

    	$this->addLabel("calendar", array(
    		"html" => SOY2Logic::createInstance("logic.CalendarLogic")->getCurrentCalendar(true)
    	));
    }

	private function _getItemObject(int $itemId){
		try{
			return self::_dao()->getById($itemId);
		}catch(Exception $e){
			$obj = new SOYCalendar_Item();
			$obj->setScheduleDate(soycalendar_get_schedule_by_array(self::_getYnjArray()));
			return $obj;
		}
	}

	/**
	 * @param int
	 * @return array
	 */
	private function _getCustomItemCheckedList(int $itemId){
		try{
			$arr = SOY2DAOFactory::create("SOYCalendar_CustomItem_CheckedDAO")->getByItemId($itemId);
		}catch(Exception $e){
			$arr = array();
		}
		if(!count($arr)) return array();

		$checkedList = array();
		foreach($arr as $obj){
			$checkedList[] = $obj->getCustomId();
		}
		
		return $checkedList;
	}

	/**
	 * @return array("year" => int, "month" => int, "day" => int)
	 */
	private function _getYnjArray(){
		if($this->error && isset($_POST["item"])) return $_POST["item"];
		return self::_getYnjArrayCommon();
	}

	/**
	 * @return array("year" => int, "month" => int, "day" => int)
	 */
	private function _getYnjArrayOnEnd(){
		if(isset($_POST["end"])) return $_POST["end"];
		return self::_getYnjArrayCommon();
 	}

	private function _getYnjArrayCommon(){
		if(isset($_GET["year"])) return array("year" => $_GET["year"], "month" => $_GET["month"], "day" => $_GET["day"]);
    	return array("year" => date("Y"), "month" => date("n"), "day" => date("j"));
	}

    private function _buildCheckboxList(){
		foreach(self::_getDayFlag() as $flg){
			$lowFlg = strtolower($flg);
			$this->addCheckBox($lowFlg, array(
	    		"name" => "repeat[day][]",
	    		"value" => $flg,
	    		"elementId" => $lowFlg,
	    		"selected" => (isset($_POST["repeat"]["day"]) && in_array($flg, $_POST["repeat"]["day"]))
	    	));
		}
    }

	private function _dao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		return $d;
	}
}
