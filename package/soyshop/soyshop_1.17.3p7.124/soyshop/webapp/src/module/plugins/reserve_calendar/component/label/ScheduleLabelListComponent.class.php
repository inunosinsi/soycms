<?php

class ScheduleLabelListComponent extends HTMLList{
	
	function populateItem($entity, $i){
		
		$this->addInput("label", array(
			"name" => "Label[label][" . $entity->getId() . "]",
			"value" => $entity->getLabel(),
			"attr:required" => "required"
		));
		
		$this->addInput("display_order", array(
			"name" => "Label[displayOrder][" . $entity->getId() . "]",
			"value" => ($entity->getDisplayOrder() < 127) ? $entity->getDisplayOrder() : ""
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&remove=" . $entity->getId() . "&item_id=" . $entity->getItemId()),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
?>