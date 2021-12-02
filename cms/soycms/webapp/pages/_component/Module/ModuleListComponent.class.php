<?php

class ModuleListComponent extends HTMLList{

	private $editorLink;
	private $removeLink;

	protected function populateItem($entity){

		$moduleId = (isset($entity["moduleId"]) && is_string($entity["moduleId"])) ? self::convertModuleId($entity["moduleId"]) : "";

		$this->addLink("module_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : null,
			"link" => "",//(isset($moduleId)) ? $this->editorLink . $moduleId : null
		));

		$this->addLabel("module_id", array(
			"text" => $moduleId
		));

		$this->addLink("module_link", array(
			"link" => (isset($moduleId)) ? $this->editorLink . $moduleId : null
		));

		$this->addLink("remove_link", array(
			"link" => (isset($moduleId)) ? $this->removeLink . $moduleId : null
		));
	}

	private function convertModuleId(string $moduleId){
		$res = strpos($moduleId, "html.");
		if(is_numeric($res) && $res === 0){
			return str_replace("html.", "", $moduleId);
		}else{
			return $moduleId;
		}
	}

	function setEditorLink($editorLink){
		$this->editorLink = $editorLink;
	}

	function setRemoveLink($removeLink){
		$this->removeLink = $removeLink;
	}
}
