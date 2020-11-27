<?php

class NoticeArrivalListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("create_date", array(
			"text" => (isset($entity["create_date"]) && is_numeric($entity["create_date"])) ? date("Y-m-d H:i", $entity["create_date"]) : ""
		));

		$this->addLink("item_name_link", array(
			"text" => (isset($entity["item_name"])) ? $entity["item_name"] : "",
			"link" => (isset($entity["item_id"])) ? SOY2PageController::createLink("Item.Detail." . $entity["item_id"]) : ""
		));

		$this->addLink("user_name_link", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : "",
			"link" => (isset($entity["id"])) ? SOY2PageController::createLink("User.Detail." . $entity["id"]) : ""
		));

		$this->addLabel("mail_address", array(
			"text" => (isset($entity["mail_address"])) ? $entity["mail_address"] : ""
		));
	}
}
