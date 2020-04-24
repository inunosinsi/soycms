<?php

class TemplateCarrierListComponent extends HTMLList{
	
	private $typeTexts;
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("carrier", array(
			"text" => ($key != "jp") ? "(" . $key .")" : ""
		));
		
		if(strpos($key, "/")) $key = str_replace("/", "_", $key);
		$query = ($key != "jp") ? "?carrier=" . $key : "";
		$this->addLink("create_template_link", array(
			"link" => SOY2PageController::createLink("Site.Template.Create") . $query,
			"text" => "テンプレートの追加"
		));
		
		$this->createAdd("template_category_list", "_common.Site.TemplateCategoryListComponent", array(
			"list" => $entity,
			"typeTexts" => $this->typeTexts,
			"carrier" => $key
		));
	}
	
	function setTypeTexts($typeTexts){
		$this->typeTexts = $typeTexts;
	}
}
?>