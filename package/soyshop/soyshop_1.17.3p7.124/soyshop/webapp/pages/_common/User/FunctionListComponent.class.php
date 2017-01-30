<?php

class FunctionListComponent extends HTMLList{
	
	protected function populateItem($entity, $key){
				
		$this->addLabel("function_title", array(
			"text" => (isset($entity["title"]) && strlen($entity["title"]) > 0) ? $entity["title"] : ""
		));
		
		$this->addLink("action_link", array(
			"link" => SOY2PageController::createLink("User.Function?moduleId=" . $key),
		));
		
		$this->addLabel("function_description", array(
			"html" => (isset($entity["description"])) ? nl2br($entity["description"]) : ""
		));
	}
}
?>