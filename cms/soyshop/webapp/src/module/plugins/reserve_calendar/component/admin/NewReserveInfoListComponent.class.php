<?php

class NewReserveInfoListComponent extends HTMLList{

	private $labels;

	function populateItem($entity, $i){
		$labelList = (isset($entity["item_id"]) && isset($this->labels[$entity["item_id"]])) ? $this->labels[$entity["item_id"]] : array();

		$itemName = (isset($entity["item_name"])) ? $entity["item_name"] : null;
		if(isset($entity["pre_reserve"]) && is_bool($entity["pre_reserve"]) && $entity["pre_reserve"]) $itemName .= "(仮予約)";
		$this->addLink("item_name", array(
			"link" => (isset($entity["schedule_id"])) ? SOY2PageController::createLink("Extension.Detail.reserve_calendar." . $entity["schedule_id"]) : null,
			"text" => $itemName
		));

		$this->addLabel("schedule_date", array(
			"text" => (isset($entity["schedule_date"])) ? date("Y-m-d", $entity["schedule_date"]) : null
		));

		$this->addLabel("label", array(
			"text" => (isset($entity["label_id"]) && isset($labelList[$entity["label_id"]])) ? $labelList[$entity["label_id"]] : null
		));

		$this->addLink("user_name", array(
			"link" => (isset($entity["user_id"])) ? SOY2PageController::createLink("User.Detail." . $entity["user_id"]) : null,
			"text" => (isset($entity["user_name"])) ? $entity["user_name"] : null
		));

		$this->addLabel("reserve_date", array(
			"text" => (isset($entity["reserve_date"])) ? date("Y-m-d H:i:s", $entity["reserve_date"]) : null
		));
	}

	function setLabels($labels){
		$this->labels = $labels;
	}
}
