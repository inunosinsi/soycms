<?php

class OSMIconListComponent extends HTMLList {

	private $iconDir;
	private $siteId;
	private $iconConfig;

	function populateItem($entity, $i){
		$filename = (is_string($entity)) ? $entity : "";
		$hash = OSMUtil::createHash($filename);
		$cfg = (isset($this->iconConfig[$hash])) ? $this->iconConfig[$hash] : array(); 

		$this->addLabel("file_name", array(
			"text" => $filename
		));

		$this->addImage("thumbnail", array(
			"src" => "/".$this->siteId."/im.php?src=".$this->iconDir.$filename."&width=30"
		));

		$this->addInput("label", array(
			"name" => "Config[".$hash."][label]",
			"value" => (isset($cfg["label"])) ? $cfg["label"] : ""
		));

		$this->addActionLink("delete_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config?openstreetmap&hash=".$hash),
			"onclick" => "return confirm('削除しても良いですか？')"
		));
	}

	function setIconDir(string $iconDir){
		$this->iconDir = $iconDir;
	}
	function setSiteId(string $siteId){
		$this->siteId = $siteId;
	}
	function setIconConfig(array $iconConfig){
		$this->iconConfig = $iconConfig;
	}
}
