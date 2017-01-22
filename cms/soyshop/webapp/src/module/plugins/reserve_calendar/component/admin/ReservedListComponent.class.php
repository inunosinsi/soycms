<?php

class ReservedListComponent extends HTMLList{
	
	private $scheduleId;
	
	function populateItem($entity, $i){

		$this->addLabel("reserve_date", array(
			"text" => (isset($entity["reserve_date"])) ? date("Y-m-d H:i:s", $entity["reserve_date"]) : ""
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
		
		$this->addLink("cancel_link", array(
			"link" => (isset($entity["id"])) ? SOY2PageController::createLink("Extension.Detail.reserve_calendar." . $this->scheduleId . "?cancel=" . $entity["id"]) : "",
			"onclick" => "return confirm('キャンセルしますか？');"
		));
	}
	
	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}
}
?>