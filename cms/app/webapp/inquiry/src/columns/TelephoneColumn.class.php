<?php

class TelephoneColumn extends SOYInquiry_ColumnBase{

	private $size1;
	private $size2;
	private $size3;

	private $attribute1;
	private $attribute2;
	private $attribute3;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

	//<input type="***">
	private $inputType = "text";

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$values = $this->getValue();
		$required = $this->getRequiredProp();

		$inputType = htmlspecialchars($this->inputType, ENT_QUOTES, "UTF-8");

		if(!is_array($values)) $values = array("", "", "");
		$tel1 = (isset($values[0])) ? $values[0] : "";
		$tel2 = (isset($values[1])) ? $values[1] : "";
		$tel3 = (isset($values[2])) ? $values[2] : "";

		$html = array();
		$html[] = "<input type=\"".$inputType."\" name=\"data[".$this->getColumnId()."][]\" value=\"".$tel1."\" size=\"".$this->size1."\" ".$this->getFormAttribute($this->attribute1)."" . $required . ">";
		$html[] = "-";
		$html[] = "<input type=\"".$inputType."\" name=\"data[".$this->getColumnId()."][]\" value=\"".$tel2."\" size=\"".$this->size2."\" ".$this->getFormAttribute($this->attribute2)."" . $required . ">";
		$html[] = "-";
		$html[] = "<input type=\"".$inputType."\" name=\"data[".$this->getColumnId()."][]\" value=\"".$tel3."\" size=\"".$this->size3."\" ".$this->getFormAttribute($this->attribute3)."" . $required . ">";
		return implode("\n",$html);
	}

	function getFormAttribute($attribute){
		return (isset($attribute) && strlen($attribute) > 0) ? str_replace("&quot;","\"",$attribute) : "";
	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	/**
	 * 個々のフォームのサイズを変更するフォーム
	 */
	function getConfigForm(){
		$html  = '幅:<input type="text" name="Column[config][size1]" value="'.$this->size1.'" size="3" />';
		$html .= '-<input type="text" name="Column[config][size2]" value="'.$this->size2.'" size="3" />';
		$html .= '-<input type="text" name="Column[config][size3]" value="'.$this->size3.'" size="3" />';

		$html .= "<br>";

		$inputType = (isset($this->inputType) && strlen($this->inputType) > 0) ? htmlspecialchars($this->inputType,ENT_QUOTES,"UTF-8") : "text";
		$html .= '<label for="Column[config][inputType]'.$this->getColumnId().'">type:</label>';
		$html .= '<input id="Column[config][inputType]'.$this->getColumnId().'" name="Column[config][inputType]" type="text" value="'.$inputType.'" style="width:10%;" /><br />';
		$html .= "※入力例: text, email, tel, number等。";

		$html .= "<br>";

		$html .= '<label for="Column[config][attribute1]'.$this->getColumnId().'">属性1:</label>';
		$html .= '<input  id="Column[config][attribute1]'.$this->getColumnId().'" name="Column[config][attribute1]" type="text" value="'.$this->attribute1.'" style="width:90%;" /><br />';
		$html .= '<label for="Column[config][attribute2]'.$this->getColumnId().'">属性2:</label>';
		$html .= '<input  id="Column[config][attribute2]'.$this->getColumnId().'" name="Column[config][attribute2]" type="text" value="'.$this->attribute2.'" style="width:90%;" /><br />';
		$html .= '<label for="Column[config][attribute3]'.$this->getColumnId().'">属性3:</label>';
		$html .= '<input  id="Column[config][attribute3]'.$this->getColumnId().'" name="Column[config][attribute3]" type="text" value="'.$this->attribute3.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\" pattern=\"\"<br>";

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
		$this->size1 = (isset($config["size1"]) && is_numeric($config["size1"])) ? (int)$config["size1"] : null;
		$this->size2 = (isset($config["size2"]) && is_numeric($config["size2"])) ? (int)$config["size2"] : null;
		$this->size3 = (isset($config["size3"]) && is_numeric($config["size3"])) ? (int)$config["size3"] : null;
		$this->attribute1 = (isset($config["attribute1"])) ? str_replace("\"","&quot;",$config["attribute1"]) : null;
		$this->attribute2 = (isset($config["attribute2"])) ? str_replace("\"","&quot;",$config["attribute2"]) : null;
		$this->attribute3 = (isset($config["attribute3"])) ? str_replace("\"","&quot;",$config["attribute3"]) : null;
		$this->inputType = (isset($config["inputType"])) && strlen($config["inputType"]) ? $config["inputType"] : "text";
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["size1"] = $this->size1;
		$config["size2"] = $this->size2;
		$config["size3"] = $this->size3;
		$config["attribute1"] = $this->attribute1;
		$config["attribute2"] = $this->attribute2;
		$config["attribute3"] = $this->attribute3;
		$config["inputType"] = $this->inputType;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function validate(){
		$values = $this->getValue();

		if(empty($values) || (is_array($values) && strlen(implode("",$values)))<1){
			$this->setValue(array("", "", ""));

			if($this->getIsRequire()){
				$this->setErrorMessage($this->getLabel()."を入力してください。");
				return false;
			}

			return true;
		}

		if( (strlen($values[0]) + strlen($values[1]) + strlen($values[2]) > 0)
		 && (strlen($values[0]) * strlen($values[1]) * strlen($values[2]) == 0)
		){
			$this->errorMessage = "電話番号の書式が不正です。";
			return false;
		}
	}

	function getErrorMessage(){
		return $this->errorMessage;
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		$value = $this->getValue();
		if(empty($value)){
			return "";
		}
		return htmlspecialchars(implode("-",$value), ENT_QUOTES, "UTF-8");
	}

	function getReplacement() {
		return (strlen($this->replacement) == 0) ? "#TELEPHONE#" : $this->replacement;
	}
}
