<?php

class ReplaceStringListComponent extends HTMLList{
	
	protected function populateItem($entity,$key){
		
		$this->addLabel("replace_string", array(
			"text" => (isset($key)) ? $key : ""
		));
		
		$this->addLabel("replace_label", array(
			"text" => (isset($entity)) ? $entity : ""
		));
	}
}
?>