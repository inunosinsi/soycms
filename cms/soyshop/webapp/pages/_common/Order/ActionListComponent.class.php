<?php

class ActionListComponent extends HTMLList{

	private $orderId;
	private $functionLink;

	function populateItem($bean){

		$this->addLink("action_link", array(
			"link" => $this->getFunctionLink($bean["moduleId"]),
			"text" => $bean["name"],
			"onclick" => (isset($bean["dialog"]) && strlen($bean["dialog"])) ? "return confirm('" . htmlspecialchars($bean["dialog"], ENT_QUOTES, "UTF-8") . "');" : "return true;",
			"target" => (isset($bean["dialog"]) && strlen($bean["dialog"])) ? "_self" : "_blank"
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
