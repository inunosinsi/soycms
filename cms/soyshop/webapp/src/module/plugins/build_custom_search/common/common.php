<?php

class CustomSearchCommon{

	const SEARCH_TYPE_TEXT = "text";
	const SEARCH_TYPE_RANGE = "range";
	const SEARCH_TYPE_SELECT = "select";
	const SEARCH_TYPE_CHECKBOX = "checkbox";
	const SEARCH_TYPE_RADIO = "radio";

	public static function getFieldConfig($flag=true){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return SOYShop_ItemAttributeConfig::load($flag);
	}

	public static function getConfig(){
		$config = SOYShop_DataSets::get("build_custom_search", null);
		$list = (isset($config)) ? soy2_unserialize($config) : array();
		if(count($list) > 0){
			$list["range_price"]["type"] = self::SEARCH_TYPE_RANGE;
			$list["range_price"]["value"] = "";

			$list["q"]["type"] = self::SEARCH_TYPE_TEXT;
			$list["q"]["value"] = "";
		}
		return $list;
	}

	public static function defaultOption(){
		return array(
			"range_price" => self::SEARCH_TYPE_RANGE,
			"item_name" => self::SEARCH_TYPE_TEXT
		);
	}

	public static function typeList(){
		return array(
			self::SEARCH_TYPE_TEXT => self::SEARCH_TYPE_TEXT,
			self::SEARCH_TYPE_RANGE => self::SEARCH_TYPE_RANGE,
			self::SEARCH_TYPE_SELECT => self::SEARCH_TYPE_SELECT,
			self::SEARCH_TYPE_CHECKBOX => self::SEARCH_TYPE_CHECKBOX,
			self::SEARCH_TYPE_RADIO => self::SEARCH_TYPE_RADIO
		);
	}

	/** build_form **/

	function buildTextForm($fieldId){
		$value = (isset($_GET[$fieldId])) ? $_GET[$fieldId] : null;

		return "<input type=\"text\" name=\"" . $fieldId . "\" value=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\">";
	}

	function buildRangeForm($fieldId){
		$html = array();

		$html[] = self::buildTextForm($fieldId . "_min");
		$html[] = " ～ ";
		$html[] = self::buildTextForm($fieldId . "_max");

		return implode("", $html);
	}

	function buildSelectBox($fieldId, $value){
		$selected = (isset($_GET[$fieldId])) ? mb_convert_encoding($_GET[$fieldId], "UTF-8", "auto") : null;

		if(strlen($value) == 0) return "";
		$array = explode("\n", $value);

		$html = array();

		$html[] = "<select name=\"" . $fieldId . "\">";

		foreach($array as $value){
			$value = rtrim($value);
			if($value == $selected){
				$html[] = "<option value=\"" . $value . "\" selected=\"selected\">" . $value . "</option>";
			}else{
				$html[] = "<option value=\"" . $value . "\">" . $value . "</option>";
			}
		}

		$html[] = "</select>";

		return implode("\n", $html);
	}

	function buildCheckBox($fieldId, $value){
		$selected = (isset($_GET[$fieldId])) ? $_GET[$fieldId] : array();

		$array = explode("\n", $value);

		$html = array();

		foreach($array as $key => $value){
			$value = rtrim($value);
			if(in_array($value, $selected)){
				$html[] = "<input type=\"checkbox\" name=\"" . $fieldId . "[]\" value=\"" . $value . "\" id=\"" . $fieldId . "_" . $key . "\" checked=\"checked\">";
			}else{
				$html[] = "<input type=\"checkbox\" name=\"" . $fieldId . "[]\" value=\"" . $value . "\" id=\"" . $fieldId . "_" . $key . "\">";
			}
			$html[] = "<label for=\"" . $fieldId . "_" . $key . "\">" . $value . "</label>\n";
		}

		return implode("", $html);
	}

	function buildRadioButton($fieldId, $value){
		$selected = (isset($_GET[$fieldId])) ? mb_convert_encoding($_GET[$fieldId], "UTF-8", "auto") : null;

		if(strlen($value) == 0) return "";
		$array = explode("\n", $value);

		$html = array();

		foreach($array as $key => $value){
			$value = rtrim($value);
			if($selected == $value){
				$html[] = "<input type=\"radio\" name=\"" . $fieldId."\" value=\"" . $value . "\" id=\"" . $fieldId . "_" . $key."\" checked=\"checked\">";
			}else{
				$html[] = "<input type=\"radio\" name=\"" . $fieldId."\" value=\"" . $value . "\" id=\"" . $fieldId . "_" . $key."\">";
			}
			$html[] = "<label for=\"" . $fieldId."_" . $key."\">" . $value . "</label>\n";
		}

		return implode("", $html);
	}

	function getConditionRadioForm(){

		$condition = (isset($_GET["custom_search"])) ? $_GET["custom_search"] : "and";

		$html = array();

		if($condition == "and"){
			$html[] = "<input type=\"radio\" name=\"custom_search\" value=\"and\" id=\"custom_search_and\" checked=\"checked\">";
		}else{
			$html[] = "<input type=\"radio\" name=\"custom_search\" value=\"and\" id=\"custom_search_and\">";
		}
		$html[] = "<label for=\"custom_search_and\">AND</label>\n";

		$html[] = "&nbsp;";

		if($condition == "or"){
			$html[] = "<input type=\"radio\" name=\"custom_search\" value=\"or\" id=\"custom_search_or\" checked=\"checked\">";
		}else{
			$html[] = "<input type=\"radio\" name=\"custom_search\" value=\"or\" id=\"custom_search_or\">";
		}
		$html[] = "<label for=\"custom_search_or\">OR</label>\n";

		return implode("", $html);
	}
}
