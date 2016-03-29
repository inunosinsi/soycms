<?php

class SendBoxMailListComponent extends HTMLList{
	
	protected function populateItem($bean){
		
		$this->createAdd("title","HTMLLink",array(
			"text" => $bean->getTitle(),
			"link" => SOY2PageController::createLink("mail.Mail.MailDetail") . "/" . $bean->getId()
		));
		
		$this->createAdd("status_text","HTMLLabel",array(
			"text" => $bean->getStatusText()
		));
		
		$this->createAdd("schedule","HTMLLabel",array(
			"text" => ($bean->getSchedule()) ? date("Y-m-d H:i:s", $bean->getSchedule()) : ""
		));
		
		$this->createAdd("mail_count","HTMLLabel",array(
			"text" => $bean->getMailCount()
		));
		
	}
	
}

?>