<?php

class SendBoxMailListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLink("title", array(
			"text" => $bean->getTitle(),
			"link" => SOY2PageController::createLink("mail.Mail.MailDetail") . "/" . $bean->getId()
		));

		$this->addLabel("status_text", array(
			"text" => $bean->getStatusText()
		));

		$this->addLabel("schedule", array(
			"text" => (is_numeric($bean->getSchedule())) ? date("Y-m-d H:i:s", $bean->getSchedule()) : ""
		));

		$this->addLabel("mail_count", array(
			"text" => $bean->getMailCount()
		));
	}
}
