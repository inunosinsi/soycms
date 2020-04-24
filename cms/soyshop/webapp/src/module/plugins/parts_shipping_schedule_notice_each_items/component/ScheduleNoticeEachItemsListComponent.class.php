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

		//entityに_coがある場合は連休
		$isConHol = (isset($entity) && is_string($entity) && strpos($entity, "_co"));
		$this->addModel("is_consecutive_holidays", array(
			"visible" => $isConHol
		));

		$this->addModel("no_consecutive_holidays", array(
			"visible" => !$isConHol
		));

		$this->addInput("consecutive_holidays", array(
			"name" => "Config[consecutive][" . $entity . "]",
			"value" => (isset($this->config["consecutive"][$entity])) ? trim($this->config["consecutive"][$entity]) : "",
			"style" => "width:50%;"
		));
	}

	function setConfig($config){
		$this->config = $config;
	}
}
