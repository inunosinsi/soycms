<?php

class StandardListComponent extends HTMLList{
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("id", array(
			"text" => (isset($entity["id"])) ? trim($entity["id"]) : null
		));
		
		$this->addInput("id_input", array(
			"name" => "Config[" . $key . "][id]",
			"value" => (isset($entity["id"])) ? trim($entity["id"]) : null
		));
		
		$this->addInput("standard", array(
			"name" => "Config[" . $key . "][standard]",
			"value" => (isset($entity["standard"])) ? trim($entity["standard"]) : null
		));
		
		$this->addInput("order", array(
			"name" => "Config[" . $key . "][order]",
			"value" => (isset($entity["order"]) && (int)$entity["order"] > 0) ? (int)$entity["order"] : ""
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=item_standard&remove=" . $key),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
?>