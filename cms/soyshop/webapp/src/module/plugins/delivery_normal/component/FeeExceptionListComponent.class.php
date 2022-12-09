<?php

class FeeExceptionListComponent extends HTMLList{

	function populateItem($entity, $key){
		$this->addLabel("pattern", array(
			"text" => (isset($entity["pattern"]) && is_numeric($entity["pattern"])) ? DeliveryNormalUtil::getPatternText($entity["pattern"]) : "---"
		));

		$this->addInput("pattern_hidden", array(
			"name" => "Change[" . $key . "][pattern]",
			"value" => $key
		));

		$this->addLabel("codes", array(
			"html" => (is_array($entity["code"])) ? self::_buildCodeInputs($entity["code"], $key) : ""
		));

		$this->addLink("remove_btn", array(
			"link" => "javascript:void(0);",
			"onclick" => "exception_pattern_remove(" . $key . ");",
			"id" => "exception_pattern_remove_button_" . $key
		));
	}

	private function _buildCodeInputs(array $codes, int $key){
		if(!count($codes)) return "";

		$html = array();
		foreach($codes as $code){
			$html[] = "<input type=\"text\" class=\"form-control\" name=\"Change[" . $key . "][code][]\" value=\"" . htmlspecialchars(trim($code), ENT_QUOTES, "UTF-8") . "\">";
		}
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"Change[" . $key . "][code][]\" value=\"\">";

		return implode("\n", $html);
	}
}
