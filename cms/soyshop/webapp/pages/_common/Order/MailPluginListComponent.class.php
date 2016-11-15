<?php

class MailPluginListComponent extends HTMLList{
	
	private $status = array();
	private $orderId;
	
	function populateItem($entity){
		
		$mailId = (isset($entity["id"])) ? $entity["id"] : "";
		$this->addLink("mail_link", array(
			"link" => SOY2PageController::createLink("Config.Mail.User?type=" . $mailId),
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));
		
		$this->addLabel("mail_title", array(
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));
		
		$this->addLabel("mail_status", array(
	   		"text" => (isset($this->status[$mailId])) ? date("Y-m-d H:i:s", $this->status[$mailId]) : "未送信"
	   	));
	   	
	   	$this->addLink("mail_send_link", array(
	   		"link" => SOY2PageController::createLink("Order.Mail." . $this->orderId . "?type=" . $mailId)
	   	));
	}
	
	function setStatus($status){
		$this->status = $status;
	}
	
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
?>