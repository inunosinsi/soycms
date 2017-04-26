<?php

class ReservedMailListComponent extends HTMLList{
	
	protected function populateItem($entity){
	
		$this->addLabel("next_send_date", array(
			"text" => (isset($entity["next_send_date"])) ? date("Y-m-d", $entity["next_send_date"]) : ""
		));
		
		$mailAddress = (isset($entity["mail_address"]) && strlen($entity["mail_address"])) ? $entity["mail_address"] : null;
		$this->addLink("to", array(
			"link" => "mailto:" . $mailAddress,
			"text" => (isset($entity["name"]) && strlen($entity["name"])) ? trim($entity["name"]) : $mailAddress
		));
		
		$this->addLink("mail_title", array(
			"link" => (isset($entity["mail_id"])) ?  CMSApplication::createLink("Mail.Detail." . $entity["mail_id"]) : null,
			"text" => (isset($entity["mail_title"])) ? $entity["mail_title"] : ""
		));
		
		$this->addLink("step_title", array(
			"link" => (isset($entity["step_id"])) ?  CMSApplication::createLink("Mail.Step." . $entity["step_id"]) . "?mail_id=" . $entity["mail_id"] : null,
			"text" => (isset($entity["step_title"])) ? $entity["step_title"] : ""
		));
	
		if(!isset($entity["next_send_date"]) || !is_numeric($entity["next_send_date"])) return false;
	}
}
?>