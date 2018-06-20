<?php

class ColumnListComponent extends HTMLList {

	private $customfields;
	private $connects;	//カラムとカスタムフィールドの連携の設定内容

	protected function populateItem($entity, $key, $i){

		$this->addLabel("label", array(
			"text" => (isset($entity)) ? $entity : ""
		));

		$this->addSelect("customfield", array(
			"name" => "Config[customfield][" . $key . "]",
			"options" => $this->customfields,
			"selected" => (isset($this->connects[$key])) ? $this->connects[$key] : ""
		));

		if(!isset($entity) || !strlen($entity)) return false;
	}

	function setCustomfields($customfields){
		$this->customfields = $customfields;
	}

	function setConnects($connects){
		$this->connects = $connects;
	}
}
