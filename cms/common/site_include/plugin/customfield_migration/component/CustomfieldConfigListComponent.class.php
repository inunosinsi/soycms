<?php

class CustomfieldConfigListComponent extends HTMLList {

	private $csfOpts;
	private $mode;
	private $config;

	protected function populateItem($entity, $key){

		$this->addLabel("label", array(
			"text" => $entity->getLabel()
		));

		$this->addLabel("id", array(
			"text" => $entity->getId()
		));

		$this->addLabel("type", array(
			"text" => (is_string($entity->getType()) && isset(CustomField::$TYPES[$entity->getType()])) ? CustomField::$TYPES[$entity->getType()] : ""
		));

		$this->addSelect("csf_select", array(
			"name" => (is_string($key)) ? $this->mode . "[" . $key . "]" : "",
			"options" => $this->csfOpts,
			"selected" => (is_string($key) && isset($this->config[$key])) ? $this->config[$key] : false
		));
	}

	function setCsfOpts($csfOpts){
		$this->csfOpts = $csfOpts;
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function setConfig($config){
		$this->config = $config;
	}
}
