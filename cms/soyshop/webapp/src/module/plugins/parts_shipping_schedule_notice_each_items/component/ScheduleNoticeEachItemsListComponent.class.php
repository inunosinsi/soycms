<?php

class ScheduleNoticeEachItemsListComponent extends HTMLList {

	private $config;

	protected function populateItem($entity){
		$this->addLabel("label", array(
			"text" => (isset($entity)) ? ShippingScheduleUtil::getLabel($entity) : ""
		));

		$this->addInput("schedule", array(
			"name" => "Config[schedule][" . $entity . "]",
			"value" => (isset($this->config["schedule"][$entity])) ? (int)$this->config["schedule"][$entity] : 1,
			"style" => "width:40px;"
		));

		$this->addTextArea("wording", array(
			"name" => "Config[notice][" . $entity . "]",
			"value" => (isset($this->config["notice"][$entity])) ? $this->config["notice"][$entity] : ""
		));
	}

	function setConfig($config){
		$this->config = $config;
	}
}
