<?php

class ReplacementStringListComponent extends HTMLList{

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

		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail") . "?plugin=replacement_string&remove=" . $key,
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
