<?php

class DraftBoxMailListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLink("title", array(
			"text" => $bean->getTitle(),
			"link" => SOY2PageController::createLink("mail.Mail") . "/" . $bean->getId()
		));

		$this->addLabel("content", array(
			"text" => $bean->getMailContent()
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($bean->getUpdateDate())) ? date("Y-m-d", $bean->getUpdateDate()) : ""
		));


		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("mail.Mail.Remove") . "/" . $bean->getId(),
			"onclick" => "return confirm('削除してもよろしいですか？');",
		));

	}
}
