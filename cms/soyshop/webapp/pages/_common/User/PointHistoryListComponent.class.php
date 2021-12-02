<?php

class PointHistoryListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s", $entity->getCreateDate()) : ""
		));

		$this->addLink("order_link", array(
			"link" => (is_numeric($entity->getOrderId()) && $entity->getOrderId() > 0) ? SOY2PageController::createLink("Order.Detail." . $entity->getOrderId()) : null
		));

		$this->addLabel("content", array(
			"text" => $entity->getContent()
		));
	}
}
