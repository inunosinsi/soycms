<?php

class HistoryOnMyPageListComponent extends HTMLList {

	private $userIds;

	protected function populateItem($entity) {
		$orderId = (is_numeric($entity->getOrderId())) ? (int)$entity->getOrderId() : 0;

		$this->addLink("order_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $orderId)
		));

		$this->addLabel("date", array(
			"text" => (is_numeric($entity->getDate())) ? date("Y-m-d H:i:s", $entity->getDate()) : ""
		));

		$userId = (isset($this->userIds[$orderId])) ? (int)$this->userIds[$orderId] : 0;
		$this->addLink("user_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $userId)
		));

		$this->addLabel("user_name", array(
			"text" => str_replace("顧客:", "", $entity->getAuthor())
		));

		$this->addLabel("content", array(
			"text" => $entity->getContent()
		));
	}

	function setUserIds($userIds){
		$this->userIds = $userIds;
	}
}
