<?php

class MailHistoryListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLabel("send_date", array(
			"text" => (is_numeric($bean->getSendDate())) ? date("Y-m-d H:i:s", $bean->getSendDate()) : ""
		));

		$this->addLink("mail_title", array(
			"link" => SOY2PageController::createLink("Order.Mail.Log." . $bean->getId()),
			"text" => $bean->getTitle()
		));
	}
}
