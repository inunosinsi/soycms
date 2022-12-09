<?php

class PaymentStatusListComponent extends HTMLList {

	private $config;

	protected function populateItem($entity, $key, $int) {
		$this->addLabel("label", array(
			"text" => (isset($entity) && is_string($entity)) ? $entity : null
		));

		$this->addInput("sort", array(
			"name" => "Sort[" . $key . "]",
			"value" => (isset($this->config[$key]) && is_numeric($this->config[$key])) ? (int)$this->config[$key] : $int,
			"attr:required" => "required",
			"style" => "width:60px;text-align:right;"
		));
	}

	function setConfig($config){
		$this->config = $config;
	}
}
