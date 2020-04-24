<?php

class SlipNumberListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$order = self::getOrderById($entity->getOrderId());
		$user = self::getUserById($order->getUserId(), $order->getStatus());

		$this->addLabel("slip_number", array(
			"text" => $entity->getSlipNumber()
		));

		$this->addLink("tracking_number", array(
			"text" => $order->getTrackingNumber(),
			"link" => SOY2PageController::createLink("Order.Detail." . $entity->getOrderId())
		));

		$this->addLabel("order_date", array(
			"text" => date("Y-m-d H:i:s", $order->getOrderDate())
		));

		$this->addLink("user_name", array(
			"text" => $user->getName(),
			"link" => SOY2PageController::createLink("User.Detail." . $order->getUserId())
		));

		$this->addLabel("status", array(
			"text" => $entity->getStatus()
		));


		$this->addActionLink("delivery_link", array(
			"link" => self::getDeliveryLink($entity->getId(), $entity->getIsDelivery()),
			"text" => ($entity->getIsDelivery() == SOYShop_SlipNumber::NO_DELIVERY) ? "発送" : "戻す",
			"onclick" => "return confirm('" . self::getConfirmText($entity->getIsDelivery()) . "')"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Extension.slip_number?remove=" . $entity->getId()),
			"onclick" => "return confirm('削除しますか？')"
		));

		//注文状態がキャンセルであれば表示しない
		if($order->getStatus() == SOYShop_Order::ORDER_STATUS_CANCELED) return false;
	}

	private function getOrderById($orderId){
		static $orders, $dao;
		if(is_null($orders)) $orders = array();
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		if(!is_numeric($orderId)) return new SOYShop_Order();
		if(isset($orders[$orderId])) return $orders[$orderId];

		try{
			$res = $dao->executeQuery("SELECT id, tracking_number, order_date, user_id, order_status FROM soyshop_order WHERE id = :id", array(":id" => $orderId));
			$orders[$orderId] = (isset($res[0])) ? $dao->getObject($res[0]) : new SOYShop_Order();
		}catch(Exception $e){
			$orders[$orderId] = new SOYShop_Order();
		}
		return $orders[$orderId];
	}

	private function getUserById($userId, $status){
		static $users, $dao;
		if(is_null($users)) $users = array();
		if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		if(!is_numeric($userId) || $status == SOYShop_Order::ORDER_STATUS_CANCELED) return new SOYShop_User();
		if(isset($users[$userId])) return $users[$userId];

		try{
			$res = $dao->executeQuery("SELECT id, name FROM soyshop_user WHERE id = :id", array(":id" => $userId));
			$users[$userId] = (isset($res[0])) ? $dao->getObject($res[0]) : new SOYShop_User();
		}catch(Exception $e){
			$users[$userId] = new SOYShop_User();
		}
		return $users[$userId];
	}

	private function getDeliveryLink($slipId, $status){
		if($status == SOYShop_SlipNumber::NO_DELIVERY){
			return SOY2PageController::createLink("Extension.slip_number?delivery=" . $slipId);
		}else{
			return SOY2PageController::createLink("Extension.slip_number?delivery=" . $slipId . "&back");
		}
	}

	private function getConfirmText($status){
		if($status == SOYShop_SlipNumber::NO_DELIVERY){
			return "発送済みにしますか？";
		}else{
			return "未発送に戻しますか？";
		}
	}
}
