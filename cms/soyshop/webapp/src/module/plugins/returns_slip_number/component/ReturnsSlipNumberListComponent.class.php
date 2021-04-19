<?php

class ReturnsSlipNumberListComponent extends HTMLList {

	private $trackingNumberList = array();
	private $userNameList = array();
	private $pairList = array();	//orderIdとuserIdのペアの一覧
	private $orderDateList = array();

	protected function populateItem($entity, $key){
		$orderId = (is_numeric($entity->getOrderId())) ? (int)$entity->getOrderId() : 0;
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

		$this->addActionLink("return_link", array(
			"link" => self::_getReturnLink($entity->getId(), $entity->getIsReturn()),
			"text" => ($entity->getIsReturn() == SOYShop_ReturnsSlipNumber::NO_RETURN) ? "返送" : "戻す",
			"onclick" => "return confirm('" . self::_getConfirmText($entity->getIsReturn()) . "')"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Extension.returns_slip_number?remove=" . $entity->getId()),
			"onclick" => "return confirm('削除しますか？')"
		));
	}

	private function _getReturnLink($slipId, $status){
		if($status == SOYShop_ReturnsSlipNumber::NO_RETURN){
			return SOY2PageController::createLink("Extension.returns_slip_number?return=" . $slipId);
		}else{
			return SOY2PageController::createLink("Extension.returns_slip_number?return=" . $slipId . "&back");
		}
	}

	private function _getConfirmText($status){
		if($status == SOYShop_ReturnsSlipNumber::NO_RETURN){
			return "返送済みにしますか？";
		}else{
			return "未返送に戻しますか？";
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
