<?php

class SlipNumberListComponent extends HTMLList {

	private $trackingNumberList = array();
	private $userNameList = array();
	private $pairList = array();	//orderIdとuserIdのペアの一覧
	private $orderDateList = array();

	protected function populateItem($entity, $key){
		$orderId = (is_numeric($entity->getOrderId())) ? (int)$entity->getOrderId() : 0;
		$order = soyshop_get_order_object($orderId);
		$userId = (isset($this->pairList[$orderId])) ? $this->pairList[$orderId] : 0;

		$this->addLabel("slip_number", array(
			"text" => $entity->getSlipNumber()
		));

		$this->addLink("tracking_number", array(
			"text" => (isset($this->trackingNumberList[$orderId])) ? $this->trackingNumberList[$orderId] : "",
			"link" => SOY2PageController::createLink("Order.Detail." . $orderId)
		));

		$this->addLabel("order_date", array(
			"text" => (isset($this->orderDateList[$orderId])) ? date("Y-m-d H:i:s", $this->orderDateList[$orderId]) : ""
		));

		$this->addLink("user_name", array(
			"text" => (isset($this->userNameList[$userId])) ? $this->userNameList[$userId] : "",
			"link" => SOY2PageController::createLink("User.Detail." . $userId)
		));

		$this->addLabel("status", array(
			"text" => $entity->getStatus()
		));


		$this->addActionLink("delivery_link", array(
			"link" => self::_getDeliveryLink($entity->getId(), $entity->getIsDelivery()),
			"text" => (is_numeric($entity->getIsDelivery()) && $entity->getIsDelivery() == SOYShop_SlipNumber::NO_DELIVERY) ? "発送" : "戻す",
			"onclick" => "return confirm('" . self::_getConfirmText($entity->getIsDelivery()) . "')"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Extension.slip_number?remove=" . $entity->getId()),
			"onclick" => "return confirm('削除しますか？')"
		));
	}

	private function _getDeliveryLink($slipId, $status){
		if($status == SOYShop_SlipNumber::NO_DELIVERY){
			return SOY2PageController::createLink("Extension.slip_number?delivery=" . $slipId);
		}else{
			return SOY2PageController::createLink("Extension.slip_number?delivery=" . $slipId . "&back");
		}
	}

	private function _getConfirmText($status){
		if($status == SOYShop_SlipNumber::NO_DELIVERY){
			return "発送済みにしますか？";
		}else{
			return "未発送に戻しますか？";
		}
	}

	function setTrackingNumberList($trackingNumberList){
		$this->trackingNumberList = $trackingNumberList;
	}
	function setUserNameList($userNameList){
		$this->userNameList = $userNameList;
	}
	function setPairList($pairList){
		$this->pairList = $pairList;
	}
	function setOrderDateList($orderDateList){
		$this->orderDateList = $orderDateList;
	}
}
