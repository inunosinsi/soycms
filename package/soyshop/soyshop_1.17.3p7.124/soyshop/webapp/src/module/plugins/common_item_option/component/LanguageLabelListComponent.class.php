<?php

class LanguageLabelListComponent extends HTMLList{
	
	private $labels;
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("label", array(
			"text" => "ラベル：" . $entity
		));
		
		$this->addInput("label_input", array(
			"name" => "Option[name_" . $key . "]",
			"value" => (isset($this->labels["name_" . $key])) ? trim($this->labels["name_" . $key]) : ""
		));
	}
	
	function setLabels($labels){
		$this->labels = $labels;
	}
}
?>