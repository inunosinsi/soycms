<?php

class ModuleListComponent extends HTMLList{

	private $moduleType;
	private $detailLink;
	private $removeLink;

	protected function populateItem($entity){
		$moduleId = (isset($entity["moduleId"]) && is_string($entity["moduleId"])) ? self::_convertModuleId($entity["moduleId"]) : "";
		$name = (isset($entity["name"]) && is_string($entity["name"])) ? $entity["name"] : "";
		if($name != $moduleId) $name .= "(". $moduleId . ")";

		$this->addLabel("name", array(
			"text" => $name
		));

		$this->addLink("edit_link", array(
			"link" => self::_getDetailLink() . $moduleId
		));
		$this->addLink("remove_link", array(
			"link" => self::_getRemoveLink() . $moduleId
		));
	}

	private function _convertModuleId(string $moduleId){
		$res = strpos($moduleId, "html.");
		if(is_bool($res) || (is_numeric($res) && $res > 0)) return $moduleId;
		return str_replace("html.", "", $moduleId);
	}

	private function _getDetailLink(){
		if(empty($this->detailLink)){
			if($this->moduleType === "html"){
				$this->detailLink = SOY2PageController::createLink("Site.Template.Module.html.Editor") . "?moduleId=";
			}else{
				$this->detailLink = SOY2PageController::createLink("Site.Template.Module.Editor") . "?moduleId=";
			}

		}

		return $this->detailLink;
	}
	private function _getRemoveLink(){
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
