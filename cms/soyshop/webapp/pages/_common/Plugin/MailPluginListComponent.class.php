<?php

class MailPluginListComponent extends HTMLList{

	private $status = array();
	private $orderId;
	private $userId;
	private $mode;

	function populateItem($entity){
		$mailId = (isset($entity["id"])) ? $entity["id"] : "";
		$postfix = ($this->mode == "order") ? "plugin" : "u_plg";
		$this->addLink("mail_link", array(
			"link" => SOY2PageController::createLink("Config.Mail.User?type=" . $mailId . "&" . $postfix),
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));

		$this->addLabel("mail_title", array(
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));

		$this->addLabel("mail_status", array(
	   		"text" => (isset($this->status[$mailId]) && is_numeric($this->status[$mailId])) ? date("Y-m-d H:i:s", $this->status[$mailId]) : "未送信"
	   	));

	   	$this->addLink("mail_send_link", array(
	   		"link" => self::_buildMailLink($mailId)
	   	));
	}

	private function _buildMailLink($mailId){
		if(is_numeric($this->orderId)){
			return SOY2PageController::createLink("Order.Mail." . $this->orderId . "?type=" . $mailId);
		}else if(is_numeric($this->userId)){
			return SOY2PageController::createLink("User.Mail." . $this->userId . "?type=" . $mailId);
		}
		return null;
	}

	function setStatus($status){
		$this->status = $status;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function setUserId($userId){
		$this->userId = $userId;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
