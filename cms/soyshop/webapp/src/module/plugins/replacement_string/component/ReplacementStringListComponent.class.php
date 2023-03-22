<?php

class ReplacementStringListComponent extends HTMLList{

	private $languages = array();

	protected function populateItem($entity, $key){
		$this->addLabel("symbol", array(
			"text" => (isset($entity["symbol"])) ? $entity["symbol"] : ""
		));

		$this->addLabel("string", array(
			"text" => (isset($entity["string"])) ? $entity["string"] : ""
		));

		$this->addInput("string_input", array(
			"name" => "string[" . $key . "]",
			"value" => (isset($entity["string"])) ? $entity["string"] : ""
		));

		$this->addLabel("string_multi_language_inputs", array(
			"html" => (is_array($entity) && is_numeric($key) && count($this->languages)) ? self::_buildMultiLangInputs($entity, $key) : ""
		));

		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail") . "?plugin=replacement_string&remove=" . $key,
			"onclick" => "return confirm('削除しますか？');"
		));
	}

	private function _buildMultiLangInputs(array $cnf, int $key){
		$h = array();
		foreach($this->languages as $lng){
			$v = (isset($cnf[$lng])) ? $cnf[$lng] : "";
			$h[] = "<td><input type=\"text\" class=\"form-control\" name=\"".$lng."[".$key."]\" value=\"".$v."\"></td>";
		}
		
		return implode("\n", $h);
	}

	function setLanguages($languages){
		$this->languages = $languages;
	}
}
