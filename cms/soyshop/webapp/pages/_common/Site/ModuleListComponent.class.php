<?php

class ModuleListComponent extends HTMLList{

	private $moduleType;
	private $detailLink;
	private $removeLink;

	protected function populateItem($entity){
		$moduleId = self::convertModuleId($entity["moduleId"]);

		$name = $entity["name"];

		if($entity["name"] != $entity["moduleId"]){
			$name = $entity["name"] . "(". $moduleId . ")";
		}

		$this->addLabel("name", array(
			"text" => $name
		));

		$this->addLink("edit_link", array(
			"link" => self::getDetailLink() . $moduleId
		));
		$this->addLink("remove_link", array(
			"link" => self::getRemoveLink() . $moduleId
		));
	}

	private function convertModuleId($moduleId){
		if(strpos($moduleId, "html.") === 0){
			return str_replace("html.", "", $moduleId);
		}else{
			return $moduleId;
		}
	}

	private function getDetailLink(){
		if(empty($this->detailLink)){
			if($this->moduleType === "html"){
				$this->detailLink = SOY2PageController::createLink("Site.Template.Module.html.Editor") . "?moduleId=";
			}else{
				$this->detailLink = SOY2PageController::createLink("Site.Template.Module.Editor") . "?moduleId=";
			}

		}

		return $this->detailLink;
	}
	private function getRemoveLink(){
		if(empty($this->removeLink)){
			if($this->moduleType === "html"){
				$this->removeLink = SOY2PageController::createLink("Site.Template.Module.html.Remove") . "?moduleId=";
			}else{
				$this->removeLink = SOY2PageController::createLink("Site.Template.Module.Remove") . "?moduleId=";
			}
		}

		return $this->removeLink;
	}

	function setModuleType($moduleType){
		$this->moduleType = $moduleType;
	}
}
?>
