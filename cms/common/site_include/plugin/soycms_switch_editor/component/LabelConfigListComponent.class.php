<?php

class LabelConfigListComponent extends HTMLList {

	private $config;

	function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? $entity->getId() : 0;

		$iconPath = (!is_null($entity->getIcon())) ? $entity->getIcon() : "default.gif";

		//icon
		$this->createAdd("icon", "HTMLImage", array(
			"src" => CMS_LABEL_ICON_DIRECTORY_URL . $iconPath,
			"alt" => $entity->getCaption()
		));

		//名称
		$this->createAdd("caption", "HTMLLabel", array(
			"text" => $entity->getCaption()
		));

		if(is_array($this->config) && array_key_exists($id, $this->config)){
			$conf = $this->config[$id];
		}else{
			$conf = 0;//デフォルト
		}

		//セレクトボックス
		$this->createAdd("config", "HTMLSelect", array(
			"options" => self::getConfig(),
			"name" => "labelConfig[". $entity->getId(). "]",
			"selected" => $conf,
			"indexOrder" => true
		));
	}

	public static function getConfig(){
		return array(
			"0" => "有効",
			"1" => "無効"
		);
	}

	function setConfig($config){
		$this->config = $config;
	}
}
