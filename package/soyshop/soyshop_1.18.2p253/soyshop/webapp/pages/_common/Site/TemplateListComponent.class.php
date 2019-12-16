<?php

class TemplateListComponent extends HTMLList{

	protected function populateItem($entity){
		$this->addLabel("name", array(
			"text" => $entity["name"]
		));

		$this->addLabel("type", array(
			"text" => $entity["type"]
		));

		$this->addLabel("url", array(
			"text" => (isset($entity["url"])) ? $entity["url"] : ""
		));

		$this->addLink("edit_link", array(
			"link" => $this->getDetailLink() . $entity["path"]
		));
		
		$path = str_replace(".html", "", $entity["path"]);
		$templateType = substr($path, 0, strpos($path, "/"));
		$templateId = substr($path, strpos($path, "/") + 1);
		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("Site.Template.Remove.?type=" . $templateType . "&id=" . $templateId)
		));
	}

	var $detailLink;

	function getDetailLink(){
		if(empty($this->detailLink)){
			$this->detailLink = SOY2PageController::createLink("Site.Template.Editor") . "/-/";
		}

		return $this->detailLink;
	}
}
?>