<?php

class ReplaceStringListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addInput("replace_key", array(
			"name" => "Replace[key][]",
			"value" => (isset($entity["key"])) ? $entity["key"] : "",
			"attr:placeholder" => "例：##REPLACE##"
		));

		$this->addInput("replace_text", array(
			"name" => "Replace[text][]",
			"value" => (isset($entity["text"])) ? $entity["text"] : ""
		));
	}
}
