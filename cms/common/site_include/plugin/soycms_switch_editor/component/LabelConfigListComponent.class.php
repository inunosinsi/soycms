<?php

class LabelConfigListComponent extends HTMLList {

	private $config;

	function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? $entity->getId() : 0;

		$iconPath = (!is_null($entity->getIcon())) ? $entity->getIcon() : "default.gif";

		//icon
		$this->addImage("icon", array(
			"src" => CMS_LABEL_ICON_DIRECTORY_URL . $iconPath,
			"alt" => $entity->getCaption()
		));

		//名称
		$this->addLabel("caption", array(
			"text" => $entity->getCaption()
		));

		//セレクトボックス
		$this->addSelect("config", array(
			"name" => "labelConfig[". $id. "]",
			"options" => self::_config(),
			"selected" => (is_array($this->config) && array_key_exists($id, $this->config)) ? $this->config[$id] : 0,
			"indexOrder" => true
		));
	}

	private function _config(){
		return array(
			"0" => "有効",
			"1" => "無効"
		);
	}

	function setConfig($config){
		$this->config = $config;
	}
}
