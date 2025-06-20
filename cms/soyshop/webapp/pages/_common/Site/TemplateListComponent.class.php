<?php

class TemplateListComponent extends HTMLList{

	protected function populateItem($entity){
		$path = (isset($entity["path"]) && is_string($entity["path"])) ? $entity["path"] : "";
		
		$this->addCheckBox("item_check", array(
			"name" => "templates[]",
            "value" => $path,
            "visible" => AUTH_OPERATE
		));

		$this->addLabel("name", array(
			"text" => $entity["name"]
		));

		$this->addLabel("type", array(
			"text" => $entity["type"]
		));

		$this->addLabel("configured_count", array(
			"text" => (strlen($path)) ? self::_logic()->getConfiguredTemplateCount($path) : 0
		));

		$this->addLabel("url", array(
			"text" => (isset($entity["url"])) ? $entity["url"] : ""
		));

		$this->addLink("edit_link", array(
			"link" => $this->getDetailLink() . $path,
			"attr:id" => (is_array($entity)) ? self::_getTemplateId($entity) : null
		));

		$path = str_replace(".html", "", $path);
		$templateType = substr($path, 0, strpos($path, "/"));
		$templateId = substr($path, strpos($path, "/") + 1);
		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("Site.Template.Remove.?type=" . $templateType . "&id=" . $templateId)
		));
	}

	private function _getTemplateId(array $array){
		if(!is_array($array) || !count($array) || !isset($array["path"]) || !strlen($array["path"])) return null;

		$id = str_replace("/", "_", $array["path"]);
		return str_replace(".html", "", $id);
	}

	var $detailLink;

	function getDetailLink(){
		if(empty($this->detailLink)){
			$this->detailLink = SOY2PageController::createLink("Site.Template.Editor") . "/-/";
		}

		return $this->detailLink;
	}

	private function _logic(){
		static $l;
		if(is_null($l)) $l = SOY2Logic::createInstance("logic.site.template.TemplateLogic");
		return $l;
	}
}
