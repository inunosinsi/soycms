<?php

class BonusListComponent extends HTMLList{
	
	protected function populateItem($entity) {
		$paymentFlag = (isset($entity["payment"]) && $entity["payment"] == SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		
		$this->addLabel("file_name", array(
			"text" => (isset($entity["filename"])) ? $entity["filename"] : ""
		));
				
		$this->addLabel("time_limit", array(
			"text" => ((isset($entity["timelimit"]))) ? date("Y年m月d日", $entity["timelimit"]) : MessageManager::get("FREEUNLIMITED")
		));
		
		
		$this->addModel("is_bonus", array(
			"visible" => ($paymentFlag)
		));
		
		$this->addLink("bonus_link", array(
			"link" => (isset($entity["url"])) ? $entity["url"] : ""
		));
		
		$this->addModel("no_bonus", array(
			"visible" => (!$paymentFlag)
		));
		
		$this->addLabel("no_download_message", array(
			"text" => $this->getBonusMessage($paymentFlag)
		));
	}
	
	function getBonusMessage($paymentFlag){
		if(!$paymentFlag) return MessageManager::get("WAITING_FOR_PAYMENT");
	}
}
?>