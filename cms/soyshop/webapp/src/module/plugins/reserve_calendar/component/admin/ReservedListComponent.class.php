<?php

class ReservedListComponent extends HTMLList{

	private $scheduleId;
	private $tempMode = false;

	function populateItem($entity, $i){

		$this->addLink("reserve_date", array(
			"link" => (isset($entity["id"])) ? SOY2PageController::createLink("Order.Detail." . $entity["id"]) : null,
			"text" => (isset($entity["reserve_date"])) ? date("Y-m-d H:i:s", $entity["reserve_date"]) : ""
		));

		$this->addLabel("seat", array(
			"text" => (isset($entity["seat"])) ? (int)$entity["seat"] : 0
		));

		$this->addLink("user_name", array(
			"link" => (isset($entity["user_id"])) ? SOY2PageController::createLink("User.Detail." . $entity["user_id"]) : null,
			"text" => (isset($entity["user_name"])) ? $entity["user_name"] : null
		));

		$this->addLink("mail_address", array(
			"link" => (isset($entity["mail_address"])) ? "mailto:" . $entity["mail_address"] : "",
			"text" => (isset($entity["mail_address"])) ? $entity["mail_address"] : ""
		));

		$this->addLabel("telephone_number", array(
			"text" => (isset($entity["telephone_number"])) ? $entity["telephone_number"] : ""
		));

		$this->addModel("is_cancel_button", array(
			"visible" => self::_checkCancelMode()
		));

		$this->addLink("cancel_link", array(
			"link" => (!$this->tempMode && isset($entity["id"])) ? SOY2PageController::createLink("Extension.Detail.reserve_calendar." . $this->scheduleId . "?cancel=" . $entity["id"]) : "",
			"onclick" => "return confirm('キャンセルしますか？');"
		));

		$this->addLink("reserve_link", array(
			"link" => ($this->tempMode && isset($entity["id"])) ? SOY2PageController::createLink("Extension.Detail.reserve_calendar." . $this->scheduleId . "?reserve=" . $entity["id"]) : "",
			"onclick" => "return confirm('本登録に変更しますか？');"
		));
	}

	private function _checkCancelMode(){
		static $on;
		if(is_null($on)){
			SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
			$cnf = ReserveCalendarUtil::getConfig();
			$on = (isset($cnf["cancel_button"]) && (int)$cnf["cancel_button"] === ReserveCalendarUtil::RESERVE_DISPLAY_CANCEL_BUTTON);
		}
		return $on;
	}

	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}

	function setTempMode($tempMode){
		$this->tempMode = $tempMode;
	}
}
