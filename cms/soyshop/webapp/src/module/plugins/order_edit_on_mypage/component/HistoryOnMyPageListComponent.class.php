<?php

class HistoryOnMyPageListComponent extends HTMLList {

	protected function populateItem($entity) {

		$this->addLink("order_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $entity->getOrderId())
		));

		$this->addLabel("date", array(
			"text" => date("Y-m-d H:i:s", $entity->getDate())
		));

		$userId = (!is_null($entity->getOrderId())) ? self::getUserIdByOrderId($entity->getOrderId()) : null;
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

	private function getUserIdByOrderId($orderId){
		static $userIds, $dao;
		if(is_null($userIds)){
			$userIds[] = array();
			$dao = new SOY2DAO();
		}
		if(isset($userIds[$orderId])) return $userIds[$orderId];

		try{
			$res = $dao->executeQuery("SELECT user_id FROM soyshop_order WHERE id = :orderId LIMIT 1", array(":orderId" => $orderId));
			$userIds[$orderId] = (isset($res[0]["user_id"])) ? (int)$res[0]["user_id"] : null;
		}catch(Exception $e){
			$userIds[$orderId] = null;
		}

		return $userIds[$orderId];
	}
}
