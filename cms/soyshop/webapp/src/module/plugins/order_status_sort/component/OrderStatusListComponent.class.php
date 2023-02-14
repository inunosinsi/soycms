<?php

class OrderStatusListComponent extends HTMLList {

	private $config;

	protected function populateItem($entity, $key, $int) {
		$this->addLabel("label", array(
			"text" => (isset($entity) && is_string($entity)) ? $entity : null
		));

		$this->addInput("sort", array(
			"name" => "Sort[" . $key . "]",
			"value" => (isset($this->config[$key]) && is_numeric($this->config[$key])) ? (int)$this->config[$key] : $int,
			"required" => "required",
			"style" => "width:60px;text-align:right;"
		));

		if($key === SOYShop_Order::ORDER_STATUS_CANCELED) return false;
	}

	function setConfig($config){
		$this->config = $config;
	}
}
