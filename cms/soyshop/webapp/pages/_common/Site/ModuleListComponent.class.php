<?php

class ModuleListComponent extends HTMLList{
	
	private $moduleType;
	private $detailLink;
	private $removeLink;
	
	protected function populateItem($entity){
		$name = $entity["name"];

		if($entity["name"] != $entity["moduleId"]){
			$name = $entity["name"] . "(". $entity["moduleId"] . ")";
		}
		
		$this->addLabel("name", array(
			"text" => $name
		));

		$this->addLink("edit_link", array(
			"link" => $this->getDetailLink() . $entity["moduleId"]
		));
		$this->addLink("remove_link", array(
			"link" => $this->getRemoveLink() . $entity["moduleId"]
		));
	}

	function getDetailLink(){
		if(empty($this->detailLink)){
			if($this->moduleType === "html"){
				$this->detailLink = SOY2PageController::createLink("Site.Template.Module.html.Editor") . "?moduleId=";
			}else{
				$this->detailLink = SOY2PageController::createLink("Site.Template.Module.Editor") . "?moduleId=";
			}
			
		}

		return $this->detailLink;
	}
	function getRemoveLink(){
		if(empty($this->removeLink)){
			if($this->moduleType === "html"){
				$this->removeLink = SOY2PageController::createLink("Site.Template.Module.Remove") . "?moduleId=";
			}else{
				$this->removeLink = SOY2PageController::createLink("Site.Template.Module.html.Remove") . "?moduleId=";
			}
		}
		
		return $this->removeLink;
	}
	
	function setModuleType($moduleType){
		$this->moduleType = $moduleType;
	}
}
?>