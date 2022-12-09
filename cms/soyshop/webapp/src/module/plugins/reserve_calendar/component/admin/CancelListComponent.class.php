<?php

class CancelListComponent extends HTMLList{

	function populateItem($entity, $i){

		$this->addLabel("cancel_date", array(
			"text" => (isset($entity["cancel_date"])) ? date("Y-m-d H:i:s", $entity["cancel_date"]) : ""
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

		//$reReserveLink = SOY2PageController::createLink("Extension.reserve_calendar?re_reserve=" . $entity["user_id"]);
		$reReserveLink = SOY2PageController::createLink("Extension.reserve_calendar");
		$param = "";
		if(isset($entity["user_id"])) $param = "re_reserve=" . $entity["user_id"];
		if(isset($entity["order_id"])){
			if(strlen($param)) $param .= "&";
			$param .= "re_order_id=" . $entity["order_id"];
		}
		if(strlen($param)) $reReserveLink .= "?" . $param;
		$this->addActionLink("re_reserve_link", array(
			"link" => $reReserveLink
		));
	}
}
