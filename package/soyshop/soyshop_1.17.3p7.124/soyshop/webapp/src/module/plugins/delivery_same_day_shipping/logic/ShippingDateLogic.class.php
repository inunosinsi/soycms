<?php

class ShippingDateLogic extends SOY2LogicBase{
	
	private $dateLogic;
	private $config;
	
	function __construct(){
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
		$this->dateLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.BusinessDateLogic");
	}
	
	/**
	 * @return businessDate, arrivalDate, description
	 */
	function get(){
		//現在の時間によって表示を出し分ける
		$tArray = explode("-", date("Y-m-d-H-i"));
		$shippingDate = time();
		
		//定休日フラグ
		$isRegularHoliday = $this->dateLogic->checkRegularHoliday($businessDate);
		
		//営業時間外フラグ 開業時間前は昨日の終業時間とみなす
		//開業時間前フラグ
		$isBeforeOpeningTime = (!$isRegularHoliday) ? self::checkBeforeOpeningTime((int)$tArray[3], (int)$tArray[4]) : false;
		
		//終業時間後フラグ
		$isAfterClosingTime = (!$isRegularHoliday && !$isBeforeOpeningTime) ? self::checkAfterClosingTime((int)$tArray[3], (int)$tArray[4]) : false;
		
		//午前フラグ	falseの場合は午後になる
		$isAm = ((int)$tArray[3] < 12);
				
		//定休日＆営業時間外
		if($isRegularHoliday || $isBeforeOpeningTime || $isAfterClosingTime){		
			$day = (int)$this->config["delivery"]["regular"]["day"];
			
			//開業時刻前のみ配送日設定の数字を午前と同じにする
			if($isBeforeOpeningTime) {
				$day = (int)$this->config["delivery"]["am"]["day"];
			}
			
			$description = $this->config["delivery"]["regular"]["description"];
			
			if($isBeforeOpeningTime){
				//何もしない
			}else{
				$shippingDate = $this->dateLogic->getNextBusinessDate(1);
			}
			
			//到着予定日
			$arrivalDate = $shippingDate + $day * 24 * 60 * 60;
			
		//営業日
		}else{
			$label = ($isAm) ? "am" : "pm";
			$day = (int)$this->config["delivery"][$label]["day"];
			$description = $this->config["delivery"][$label]["description"];
			
			//午後の注文の場合は一日足す
			if($label == "pm"){
				$shippingDate += 24*60*60;
			}
						
			//到着予定日
			$arrivalDate = strtotime("+" . $day . " day");
		}
		
		return array($shippingDate, $arrivalDate, $description);
	}
	
	private function checkBeforeOpeningTime($hour, $min){
		$startConf = $this->config["businessHour"]["start"];
		
		//時が下だった場合はtrue
		if($hour < (int)$startConf["hour"]) return true;
		
		//時が一緒だった場合は分を調べる
		if($hour === (int)$startConf["hour"] && $min < (int)$startConf["min"]) return true;
		
		return false;
	}
	
	private function checkAfterClosingTime($hour, $min){
		$endConf = $this->config["businessHour"]["end"];
		
		//時が上だった場合はtrue
		if($hour > (int)$endConf["hour"]) return true;
		
		//時が一緒だった場合は分を調べる
		if($hour === (int)$endConf["hour"] && $min > (int)$endConf["min"]) return true;
		
		return false;
	}
	
	function convertDescription($values){
		//発送予定日の配列
		$bArray = explode("-", date("Y-n-j", $values[0]));
		
		//到着予定日の配列
		$aArray = explode("-", date("Y-n-j", $values[1]));
				
		//説明文
		$description = str_replace("#SHIPPING_YEAR#", $bArray[0], $values[2]);
		$description = str_replace("#SHIPPING_MONTH#", $bArray[1], $description);
		$description = str_replace("#SHIPPING_DAY#", $bArray[2], $description);
		$description = str_replace("#ARRIVAL_YEAR#", $aArray[0], $description);
		$description = str_replace("#ARRIVAL_MONTH#", $aArray[1], $description);
		return str_replace("#ARRIVAL_DAY#", $aArray[2], $description);
	}
		
	function setConfig($config){
		$this->config = $config;
	}
}
?>