<?php

class MessageListComponent extends HTMLList{
	
	private $accountList;
	
	protected function populateItem($entity, $idx) {
		
		$account = (isset($this->accountList[$entity->getAccountId()])) ? $this->accountList[$entity->getAccountId()] : array("name" => "", "mailaddress" => "");
		
		$this->addLabel("name", array(
			"text" => $account["name"]
		));
		
		$this->addLabel("create_date", array(
			"text" => date("Y年m月d日H:i:s", $entity->getCreateDate())
		));
		
		$this->addLabel("message", array(
			"html" => nl2br($entity->getMessage())
		));
		
	}
	
	function setAccountList($accountList){
		$this->accountList = $accountList;
	}
}
?>