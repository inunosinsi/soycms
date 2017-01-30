<?php

class PointHistoryListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getCreateDate())
		));
		
		$this->addLink("order_link", array(
			"link" => (!is_null($entity->getOrderId())) ? SOY2PageController::createLink("Order.Detail." . $entity->getOrderId()) : null
		));
		
		$this->addLabel("content", array(
			"text" => $entity->getContent()
		));
	}
}
?>