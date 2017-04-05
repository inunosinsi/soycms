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
			"link" => "?replacement_string&remove=" . $key . "#config",
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
?>