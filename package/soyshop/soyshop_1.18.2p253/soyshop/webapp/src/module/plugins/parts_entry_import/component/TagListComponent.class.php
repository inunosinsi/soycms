<?php

class TagListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLabel("id", array(
			"text" => (isset($entity["0"])) ? $entity["0"] : ""
		));
		
		$this->addLabel("tag", array(
			"text" => (isset($entity["1"])) ? $entity["1"] : ""
		));
		
		$this->addLabel("description", array(
			"text" => (isset($entity["2"])) ? $entity["2"] : ""
		));
	}
}
?>