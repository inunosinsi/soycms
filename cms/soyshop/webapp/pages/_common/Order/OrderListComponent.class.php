<?php

class OrderListComponent extends HTMLList{

	private $dao;
	private $userNameList = array();

	private $orderDetailLink;
	private $orderMailLink;
	private $userLink;

	protected function populateItem($order){

		$this->addInput("order_check", array(
			"name" => "orders[]",
			"value" => $order->getId(),
			"onchange" => '$(\'#orders_operation\').show();',
		));

		$this->addLabel("id", array(
			"text" => $order->getId(),
		));

		$this->addLabel("order_id", array(
			"text" => $order->getTrackingNumber()
		));

		$detailLink = self::_getOrderDetailLink($order->getId());
		$this->addLink("order_id_link", array(
			"text" => $order->getTrackingNumber(),
			"link" => $detailLink
		));

		$this->addLabel("order_date", array(
			"text" => (is_numeric($order->getOrderDate())) ? date('Y-m-d H:i', $order->getOrderDate()) : ""
		));

		$this->addLink("detail_link", array(
			"link" => $detailLink
		));

		$this->addLabel("order_status", array(
			"text" => $order->getOrderStatusText()
		));

		$this->addLabel("payment_status", array(
			"text" => $order->getPaymentStatusText()
		));

		$mailStatus = $order->getMailStatusList();
		$this->addLink("payment_mail_status", array(
			"text" => (isset($mailStatus["payment"])) ? "済" : "未送信",
			"link" => ( (isset($mailStatus["payment"])) ? self::_getOrderDetailLink($order->getId()) . "#mail_status" : self::_getOrderMailLink($order->getId())."?type=" . SOYShop_Order::SENDMAIL_TYPE_PAYMENT)
		));

		$this->addLink("confirm_mail_status", array(
			"text" => (isset($mailStatus["confirm"])) ? "済" : "未送信",
			"link" => ( (isset($mailStatus["confirm"])) ? self::_getOrderDetailLink($order->getId()) . "#mail_status" : self::_getOrderMailLink($order->getId())."?type=" . SOYShop_Order::SENDMAIL_TYPE_CONFIRM)
		));

		$this->addLink("delivery_mail_status", array(
			"text" => (isset($mailStatus["delivery"])) ? "済" : "未送信",
			"link" => ( (isset($mailStatus["delivery"])) ? self::_getOrderDetailLink($order->getId()) . "#mail_status" : self::_getOrderMailLink($order->getId())."?type=" . SOYShop_Order::SENDMAIL_TYPE_DELIVERY)
		));

		$userId = (is_numeric($order->getUserId())) ? $order->getUserId() : 0;
		$this->addLink("customer_link", array(
			"link" => self::_getUserLink() . "/" . $userId,
			"text" => (isset($this->userNameList[$userId])) ? $this->userNameList[$userId] : ""
		));

		$this->addLabel("order_price", array(
			"text" => soy2_number_format($order->getPrice())
		));
	}

	private function _getOrderDetailLink($id = null){
		if(!$this->orderDetailLink) $this->orderDetailLink = SOY2PageController::createLink("Order.Detail");
		return ($id > 0) ? $this->orderDetailLink . "/$id" : $this->orderDetailLink ;
	}

	private function _getOrderMailLink($id = null){
		if(!$this->orderMailLink) $this->orderMailLink = SOY2PageController::createLink("Order.Mail");
		return ($id > 0) ? $this->orderMailLink . "/$id" : $this->orderMailLink ;
	}

	private function _getUserLink(){
		if(!$this->userLink) $this->userLink = SOY2PageController::createLink("User.Detail");
		return $this->userLink;
	}

	function setUserNameList($userNameList){
		$this->userNameList = $userNameList;
	}
}
