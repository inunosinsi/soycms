<?php

class TemplateListComponent extends HTMLList{
	
	private $editLink;
	private $mode;
	
	protected function populateItem($entity){
		
		$appId = $this->getAppId($entity);
		$this->getFileName($entity);
		
		$this->addLabel("app_id", array(
			"text" => $appId
		));
		
		$this->addLabel("name", array(
			"text" => str_replace($appId . ".", "", $entity)
		));
		
		$this->addLink("edit_link", array(
			"link" => $this->editLink . "/-/" . $this->mode . "/" . $appId . "/" . $this->getFileName($entity)
		));
	}
	
	function getAppId($file){
		return substr($file, 0, strpos($file, "."));
	}
	
	function getFileName($file){
		preg_match('/\.(.*)Page\./', $file, $tmp);
		return (isset($tmp[1])) ? $tmp[1] : "";
	}
	
	function setEditLink($editLink){
		$this->editLink = $editLink;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
?>