<?php

class DateWithoutDayColumn extends SOYInquiry_ColumnBase{

	//年のセレクトボックスに表示する年の設定
	private $startYear;
	private $endYear;

	private $hasToday;

	private $attribute;

	private $labels = array("y" =>"----", "m" => "--");

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attributes = array()){

		$config = $this->getDateConfig();
		$startYear = $config["startYear"];
		$endYear = $config["endYear"];

		$attributes = $this->getAttributes();
		$required = $this->getRequiredProp();

		$values = $this->getValue();

		if(!is_array($values)){
			$hasToday = $this->hasToday;

			$value = array();

			//ディフォルトで今日を表示する
			if(isset($hasToday)){
				//設定した表示年数に今日があるかチェックする
				if(date("Y") >= $startYear && date("Y") <= $endYear){
					$values = array("year" => date("Y"), "month" => date("m"));
				}
			}else{
				$values = array("year" => "", "month" => "");
			}
		}

		$html = array();
		$html[] = "<select name=\"data[".$this->getColumnId()."][year]\" ".implode(" ",$attributes)."" . $required . ">";
		$html[] ="<option value=\"\">" . $this->labels["y"] . "</option>";

		for($i = $startYear; $i <= $endYear; $i++){
			if(isset($values["year"]) && $values["year"] == $i){
				$html[] ="<option selected=\"selected\">".$i."</option>";
			}else{
				$html[] ="<option>".$i."</option>";
			}
		}
		$html[] = "</select>";

		$html[] = "<select name=\"data[".$this->getColumnId()."][month]\" ".implode(" ",$attributes)."" . $required . ">";
		$html[] ="<option value=\"\">" . $this->labels["m"] . "</option>";
		for($i = 1; $i <= 12; $i++){
			if(isset($values["month"]) && $values["month"] == $i){
				$html[] = "<option selected=\"selected\">" . sprintf("%0d",$i) . "</option>";
			}else{
				$html[] = "<option>" . sprintf("%0d",$i) . "</option>";
			}
		}
		$html[] = "</select>";

		return implode("\n",$html);
	}

	function getAttributes(){
		$attributes = array();

		//設定したattributeを挿入
		if(isset($this->attribute) && strlen($this->attribute) > 0){
			$attribute = str_replace("&quot;","\"",$this->attribute);	//"が消えてしまうから、htmlspecialcharsができない
			$attributes[] = trim($attribute);
		}

		return $attributes;
	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		$values = $this->getValue();
		if(!isset($values["year"]) || !isset($values["month"])){
			return "----/--";
		}else{
			return htmlspecialchars($values["year"] . "/" . $values["month"], ENT_QUOTES, "UTF-8");
		}
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$hasToday = $this->hasToday;

		$html  = "表示年数:";
		$html .= '<input type="text" name="Column[config][startYear]" value="'.$this->startYear.'" size="4" />';
		$html .= "から";
		$html .= '<input type="text" name="Column[config][endYear]" value="'.$this->endYear.'" size="4" />まで<br>';

		$html .= "空の値の表示設定:";
		$html .= '年:<input type="text" name="Column[config][labels][y]" value="'.$this->labels["y"].'" size="3"> ';
		$html .= '月:<input type="text" name="Column[config][labels][m]" value="'.$this->labels["m"].'" size="3"><br>';

		if(isset($hasToday)){
			$html .= '<input type="checkbox" name="Column[config][hasToday]" value="1" checked="checked" />';
		}else{
			$html .= '<input type="checkbox" name="Column[config][hasToday]" value="1" />';
		}

		$html .= "今日の日付にselected属性を付ける";

		$html .= "<br />";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim($this->attribute);
		}

		$html .= '<label for="Column[config][style]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input id="Column[config][style]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\"<br>";

		$html .= '<label><input type="checkbox" name="Column[config][requiredProp]" value="1"';
		if($this->requiredProp){
			$html .= ' checked';
		}
		$html .= '>required属性を利用する</label>';

		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);

		$this->startYear = (isset($config["startYear"]) && is_numeric($config["startYear"])) ? (int)$config["startYear"] : null;
		$this->endYear = (isset($config["endYear"]) && is_numeric($config["endYear"])) ? (int)$config["endYear"] : null;
		$this->hasToday = isset($config["hasToday"]) ? 1 : null;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
		$this->labels = (isset($config["labels"]) && is_array($config["labels"])) ? $config["labels"] : array("y" => "----", "m" => "--");
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["startYear"] = $this->startYear;
		$config["endYear"] = $this->endYear;
		$config["hasToday"] = $this->hasToday;
		$config["attribute"] = $this->attribute;
		$config["labels"] = $this->labels;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function validate(){
		$values = $this->getValue();

		if($this->getIsRequire()){
			if(
				empty($values)
				|| !strlen(@$values["year"])
				|| !strlen(@$values["month"])
			){
				$this->setErrorMessage($this->getLabel()."を入力してください。");
				return false;
			}
		}

		return true;
	}

    function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  => "連携しない",
		);
	}

	function factoryConverter() {
		return new DateConverter();
	}

	function factoryConnector(){
		return new DateConnector();
	}

	/**
	 * 日付表示の設定を取得する
	 */
	function getDateConfig(){

		$startYear = $this->startYear;
		$endYear = $this->endYear;

		if(!is_null($startYear) && !$endYear){
			$endYear = date("Y");
		}

		if(!$startYear && !is_null($endYear)){
			$startYear = date("Y");
		}

		//終りの年が正しいかチェックする
		if($startYear >= $endYear){
			$startYear = null;
			$endYear = null;
		}

		//管理画面でフォームに未入力の場合
		if(!$startYear) $startYear = "1900";
		if(!$endYear) $endYear = date("Y");

		$dateConfig = array();
		$dateConfig["startYear"] = $startYear;
		$dateConfig["endYear"] = $endYear;

		return $dateConfig;
	}
}
