<?php

class DateWithoutYearColumn extends SOYInquiry_ColumnBase{

	private $hasToday;

	private $attribute;

	private $labels = array("m" => "--", "d" => "--");

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

    /**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){
	
		$attributes = $this->getAttributes();
		$required = $this->getRequiredProp();

		$values = $this->getValue();
		if(is_array($values) && count($values) < 2){
			$value = array();

			//ディフォルトで今日を表示する
			if($this->hasToday){
				$values = array("month" => date("m"), "day" => date("j"));
			}else{
				$values = array("month" => "", "day" => "");
			}
		}

		$html = array();
		
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

		$html[] = "<select name=\"data[".$this->getColumnId()."][day]\" ".implode(" ",$attributes)."" . $required . ">";
		$html[] ="<option value=\"\">" . $this->labels["d"] . "</option>";
		for($i = 1; $i <= 31; $i++){
			if(isset($values["day"]) && $values["day"] == $i){
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
			$attribute = str_replace("&quot;","\"",$this->attribute);	// ダブルクォーテーションが消えてしまうから、htmlspecialcharsができない
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
		if(!isset($values["month"]) || !isset($values["day"])){
			return "--/--";
		}else{
			return htmlspecialchars($values["month"] . "/" . $values["day"], ENT_QUOTES, "UTF-8");
		}
	}

	/**「
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "空の値の表示設定:";
		$html .= '月:<input type="text" name="Column[config][labels][m]" value="'.$this->labels["m"].'" size="3">';
		$html .= '日:<input type="text" name="Column[config][labels][d]" value="'.$this->labels["d"].'" size="3"><br>';

		if($this->hasToday){
			$html .= '<input type="checkbox" name="Column[config][hasToday]" value="1" checked="checked" />';
		}else{
			$html .= '<input type="checkbox" name="Column[config][hasToday]" value="1" />';
		}

		$html .= "今日の日付にselected属性を付ける";

		$html .= "<br />";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim((string)$this->attribute);
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
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->hasToday = (isset($config["hasToday"]) && $config["hasToday"]);
		$this->attribute = (isset($config["attribute"]) && is_string($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : "";
		$this->labels = (isset($config["labels"]) && is_array($config["labels"])) ? $config["labels"] : array("m" => "--", "d" => "--");
		$this->requiredProp = (isset($config["requiredProp"]) && $config["requiredProp"]);
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["hasToday"] = $this->hasToday;
		$config["attribute"] = $this->attribute;
		$config["labels"] = $this->labels;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function validate(){
		if(!$this->getIsRequire()) return true;

		$values = $this->getValue();

		if(
			count($values) < 2
			|| !strlen(@$values["month"])
			|| !strlen(@$values["day"])
		){
			switch(SOYCMS_PUBLISH_LANGUAGE){
				case "en":
					$msg = "Please enter the ".$this->getLabel().".";
					break;
				default:
					$msg = $this->getLabel() . "を入力してください。";
			}
			
			$this->setErrorMessage($msg);
			return false;
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
}
