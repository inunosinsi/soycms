<?php

class ImportLogic extends SOY2LogicBase {

	private $itemId;

	function __construct(){
		SOY2::imports("module.plugins.reserve_calendar.domain.*");
	}

	function import(){
		if(!isset($_FILES["import"]["name"]) || !stripos($_FILES["import"]["name"], ".csv")) return false;

		set_time_limit(0);

		$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");
		$logic->setCharset("Shift-JIS");

		$fileContent = file_get_contents($_FILES["import"]["tmp_name"]);
        unlink($_FILES["import"]["tmp_name"]);

		$labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($this->itemId);

		$lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines
		if(!count($lines)) return true;		//登録する情報なし

		$schDao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
		$schPriceDao = SOY2DAOFactory::create("SOYShopReserveCalendar_SchedulePriceDAO");
		$extLabels = self::extentionLabels();	//金額の拡張分

		foreach($lines as $line){
			$line = $logic->encodeFrom($line);
			$values = explode(",", $line);
			if(!isset($values[0])) continue;
			$dateValue = self::convert($values[0]);
			if(!strlen($dateValue)) continue;
			preg_match('/^\d{4}/', $dateValue, $tmp);	//配列の0の値で西暦(2019)の値があるか？
			if(!isset($tmp[0])) continue;

			//諸々の値が数字であるかをチェック
			if(!self::checkInteger($values)) continue;

			if(!isset($labelList[$values[1]])) continue;	//ラベルのチェック

			$dateArray = explode("-", $dateValue);

			//ここから登録
			$sch = new SOYShopReserveCalendar_Schedule();
			$sch->setItemId($this->itemId);
			$sch->setLabelId($values[1]);
			$sch->setPrice($values[3]);
			$sch->setYear((int)$dateArray[0]);
			$sch->setMonth((int)$dateArray[1]);
			$sch->setDay((int)$dateArray[2]);
			$sch->setUnsoldSeat($values[2]);

			try{
				$schId = $schDao->insert($sch);
			}catch(Exception $e){
				continue;
			}

			if(count($extLabels)){
				$cnt = 4;	//lineのidxの4から金額の拡張分が開始する
				foreach($extLabels as $key => $label){	//子供料金等のラベル
					$extPrice = (isset($values[$cnt])) ? $values[$cnt++] : "";
					if(!strlen($extPrice) || !is_numeric($extPrice)) continue;

					$schPriceObj = new SOYShopReserveCalendar_SchedulePrice();
					$schPriceObj->setScheduleId($schId);
					$schPriceObj->setLabel($label);
					$schPriceObj->setFieldId($key);
					$schPriceObj->setPrice($extPrice);
					try{
						$schPriceDao->insert($schPriceObj);
					}catch(Exception $e){
						//
					}
				}
			}
		}

		return true;
	}

	private function extentionLabels(){
		//プラグインで項目を更に増やせるようにしたい
		SOYShopPlugin::load("soyshop.add.price.on.calendar");
		$items = SOYShopPlugin::invoke("soyshop.add.price.on.calendar", array(
			"mode" => "csv",
		))->getList();

		if(!count($items)) return array();

		$list = array();
		foreach($items as $moduleId => $v){
			$list[$v["key"]] = $v["label"];
		}
		return $list;
	}

	private function convert($v){
		$before = array("０", "１", "２", "３", "４", "５", "６", "７", "８", "９", "ー");
		$after = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-");
		for($i = 0; $i < count($before); $i++){
			$v = str_replace($before[$i], $after[$i], $v);
		}
		return $v;
	}

	private function checkInteger($values){
		if(count($values) < 2) return false;
		for($i = 1; $i < count($values); $i++){
			if($i >= 3) continue;	//金額の拡張分から調べない
			if(!is_numeric($values[$i])) return false;
		}
		return true;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
