<?php

class ColumnListComponent extends HTMLList {

	private $customfields;
	private $connects;	//カラムとカスタムフィールドの連携の設定内容

	protected function populateItem($entity, $key, $i){

		$this->addLabel("label", array(
			"text" => (isset($entity)) ? $entity : ""
		));

		$fieldId = (isset($this->connects[$key])) ? $this->connects[$key] : "";
		$this->addLabel("create_date_annotation", array(
			"html" => ($key == "create_date" && strlen($fieldId)) ? self::getAnnotation($fieldId) : ""
		));

		$this->addSelect("customfield", array(
			"name" => "Config[customfield][" . $key . "]",
			"options" => $this->customfields,
			"selected" => $fieldId
		));

		if(!isset($entity) || !strlen($entity)) return false;
	}

	private function getAnnotation($fieldId){
		$fieldId = (strlen($fieldId)) ? $fieldId : "***";
		return "<br>※<strong>cms:id=\"" . $fieldId . "_inquiry_date\"</strong> cms:format使用可<br>".
		"cms:formatの使用方法は<a href=\"https://www.soycms.net/man/use_list/block\" target=\"_blank\">SOY CMSマニュアルのよく使う項目</a>をご確認ください。";
	}

	function setCustomfields($customfields){
		$this->customfields = $customfields;
	}

	function setConnects($connects){
		$this->connects = $connects;
	}
}
