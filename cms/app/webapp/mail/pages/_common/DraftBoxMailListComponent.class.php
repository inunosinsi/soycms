<?php

class DraftBoxMailListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->createAdd("title","HTMLLink",array(
			"text" => $bean->getTitle(),
			"link" => SOY2PageController::createLink("mail.Mail") . "/" . $bean->getId()
		));

		$this->createAdd("content","HTMLLabel",array(
			"text" => $bean->getMailContent()
		));

		$this->createAdd("update_date","HTMLLabel",array(
			"text" => date("Y-m-d", $bean->getUpdateDate())
		));


		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("mail.Mail.Remove") . "/" . $bean->getId(),
			"onclick" => "return confirm('削除してもよろしいですか？');",
		));

	}

}

?>
