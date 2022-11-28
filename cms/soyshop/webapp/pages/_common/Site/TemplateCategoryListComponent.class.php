<?php

class TemplateCategoryListComponent extends HTMLList{
	
	private $typeTexts;
	private $carrier;
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("template_type", array(
			"text" => $this->typeTexts[$key]
		));
		
		$this->createAdd("template_list", "_common.Site.TemplateListComponent", array(
			"list" => $entity
		));
		
		$query = ($this->carrier != "jp") ? "&carrier=" . $this->carrier : "";
		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Site.Template.Create?type=" . $key) . $query
		));
	}
	
	function setTypeTexts($typeTexts){
		$this->typeTexts = $typeTexts;
	}
	
	function setCarrier($carrier){
		$this->carrier = $carrier;
	}
}
?>