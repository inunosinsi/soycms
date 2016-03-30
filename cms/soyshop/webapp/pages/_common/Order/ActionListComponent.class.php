<?php

class ActionListComponent extends HTMLList{

	private $orderId;
	private $functionLink;

	function populateItem($bean){

		$this->addLink("action_link", array(
			"link" => $this->getFunctionLink($bean["moduleId"]),
			"text" => $bean["name"]
		));
	}

	function getFunctionLink($moduleId){
		if(!$this->functionLink) $this->functionLink = SOY2PageController::createLink("Order.Function." . $this->orderId);
		return $this->functionLink . "?moduleId=" . $moduleId;
	}

	function getOrderId() {
		return $this->orderId;
	}
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}
}
?>