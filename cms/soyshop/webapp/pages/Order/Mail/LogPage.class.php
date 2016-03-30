<?php

class LogPage extends WebPage{
	
	private $logId;
	
	function LogPage($args){
		$this->logId = (isset($args[0])) ? $args[0] : null;
		
		WebPage::WebPage();
		
		$mailLogDao = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");
		try{
			$log = $mailLogDao->getbyId($this->logId);
		}catch(Exception $e){
			SOY2PageController::jump("Order");
		}
		
		$this->addLink("order_detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $log->getId())
		));
		
		$this->addLabel("send_date", array(
			"text" => date("Y-m-d H:i:s", $log->getSendDate())
		));
		
		$recipients = explode(",", $log->getRecipient());
		
		$mails = array();
		foreach($recipients as $recipient){
			$mails[] = "<a href=\"mailto:" . $recipient . "\">" . $recipient . "</a>";
		}
		
		$this->addLabel("recipient", array(
			"html" => implode("<br />", $mails)
		));
		
		$this->addLabel("title", array(
			"text" => $log->getTitle()
		));
		
		$this->addLabel("content", array(
			"html" => nl2br($log->getContent())
		));
	}
}
?>