<?php

class CalendarColumnLogic extends SOY2LogicBase {

	CONST STANDARD_DATE_FORMAT = "mm/dd/yy";

	private $columnId;

	/**
	 * @return string
	 */
	function extractColumnNumber(){
		return str_replace("column_", "", self::_column($this->columnId)->getColumnId());
	}

	/**
	 * @return string
	 */
	function createIdPropertyName(){
		return "date-input-".self::extractColumnNumber();
	}

	function  getDateFormatDefault(){
		return self::STANDARD_DATE_FORMAT;
	}
	
	/**
	 * @return string
	 */
	function getDateFormat(){
		$cfg = self::_getConfig();
		return (isset($cfg["dateFormat"])) ? $cfg["dateFormat"] : self::STANDARD_DATE_FORMAT;
	}

	function getDateFormatOtherLanguage(){
		$cfg = self::_getConfig();
		return (isset($cfg["dateFormatOtherLanguage"])) ? $cfg["dateFormatOtherLanguage"] : self::STANDARD_DATE_FORMAT;
	}

	/**
	 * @param int
	 * @return string
	 */
	function buildJsCode(){
		if($this->columnId <= 0) return "";
		
		$column = self::_column($this->columnId);
		if(!is_numeric($column->getId())) return "";

		return self::_buildJQueryUIDatePickerJs($column);
	}

	/**
	 * @param SOYInquiry_Column
	 * @return string
	 */
	private function _buildJQueryUIDatePickerJs(SOYInquiry_Column $column){
		$code = array();

		$cfg = self::_getConfig();

		// 選択不可日(手動)
		$disabledDates = (isset($cfg["disabledDates"])) ? self::_splitDisabledDates($cfg["disabledDates"]) : array();
		$code[] = "let disabledDates_".self::extractColumnNumber()." = [];";
		if(count($disabledDates)){
			foreach($disabledDates as $date){
				$code[] = "disabledDates_".self::extractColumnNumber().".push(\"".$date."\");";
			}
		}

		$code[] = "$(\"#date-input-".self::extractColumnNumber()."\").datepicker({";
		$code[] = "	showAnim: \"slideDown\",";

		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) {
			$lng = (defined("SOYSHOP_PUBLISH_LANGUAGE")) ? SOYSHOP_PUBLISH_LANGUAGE : "jp";
			define("SOYCMS_PUBLISH_LANGUAGE", $lng);
		}

		$fmt = (SOYCMS_PUBLISH_LANGUAGE == "jp") ? self::getDateFormat() : self::getDateFormatOtherLanguage();
		if(strlen($fmt) && $fmt != self::STANDARD_DATE_FORMAT) $code[] = "	dateFormat: \"".$fmt."\",";
		
		
		$code[] = "	changeMonth: true,";
		$code[] = "	changeYear: true,";

		// 定休日
		$disabledDays = (isset($cfg["disabledDays"]) && is_array($cfg["disabledDays"])) ? $cfg["disabledDays"] : array();
		if(count($disabledDays) || count($disabledDates)){
			$code[] = "	beforeShowDay: function(date) {";
			if(count($disabledDays)){
				$code[] = "		let day = date.getDay();";
				foreach($disabledDays as $d){
					$code[] = "		if (day == ".$d.") {";
					$code[] = "			return [false, \"\", \"\"];";
					$code[] = "		}";
				}
			}
			if(count($disabledDates)){
				$code[] = "		if (disabledDates_".self::extractColumnNumber().".indexOf($.datepicker.formatDate('yy-mm-dd', date)) !== -1) {";
				$code[] = "			return [false, \"\", \"\"];";
				$code[] = "		}";
			}
			$code[] = "		return [true, \"\", \"\"];";
			$code[] = "	},";
		}

		// 本日より前の日を選択可にする
		if(!isset($cfg["allowPastDates"]) || (int)$cfg["allowPastDates"] <= 0){
			// 最短選択可能日
			$leadTimeDays = (isset($cfg["leadTimeDays"])) ? (int)$cfg["leadTimeDays"] : 0;
			// 最短選択可能日に時刻による加算
			$add = (isset($cfg["leadTimeDaysAdd"])) ? (int)$cfg["leadTimeDaysAdd"] : 0;
			if($add > 0){
				// 時間と分の設定
				$cutoffAdjustment = (isset($cfg["cutoffAdjustment"]) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $cfg["cutoffAdjustment"])) ? $cfg["cutoffAdjustment"] : "";
				if(strlen($cutoffAdjustment)){
					$_arr = explode(":", $cutoffAdjustment);
					if(count($_arr) === 2){
						//時間の方の確認
						if((int)date("H") > (int)$_arr[0]){
							$leadTimeDays++;
						//分の方の確認	
						}else if((int)date("H") === (int)$_arr[0] && (int)date("i") >= (int)$_arr[1]){
							$leadTimeDays++;
						}
					}
				}
			}
			
			if($leadTimeDays >= 0){
				$code[] = "	minDate: \"+".$leadTimeDays."d\",";
			}
		}
		$code[] = "});";
		return implode("\n", $code);
	}

	/**
	 * @param string
	 * @return array
	 */
	private function _splitDisabledDates(string $dateString){
		$dateString = trim($dateString);
		if(!strlen($dateString)) return array();

		$dateString = str_replace(array("、"), ",", $dateString);
		$dateString = str_replace(array("　", " "), "", $dateString);
		$_arr = explode(",", $dateString);
		if(!count($_arr)) return array();

		$dates = array();
		foreach($_arr as $v){
			$v = trim($v);
			if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $v)){
				$dates[] = $v;
			}
		}
		return $dates;
	}

	/**
	 * @param int
	 * @return SOYInquiry_Column
	 */
	private function _column(int $columnId){
		static $cols;
		if(!is_array($cols)) $cols = array();
		if(isset($cols[$columnId]) && $cols[$columnId] instanceof SOYInquiry_Column) return $cols[$columnId];
		
		try{
			$col = SOY2DAOFactory::create("SOYInquiry_ColumnDAO")->getById($columnId);
		}catch(Exception $e){
			$col =  new SOYInquiry_Column();
		}
		$cols[$columnId] = ($col->getType() == "Calendar") ? $col : new SOYInquiry_Column();
		return $cols[$columnId];
	}

	private function _getConfig(){
		$cfg = self::_column($this->columnId)->getConfig();
		return (is_array($cfg)) ? $cfg : soy2_unserialize((string)$cfg);
	}

	function setColumnId(int $columnId){
		$this->columnId = $columnId;
	}
}
