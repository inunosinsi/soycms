<?php

class OrderListComponent extends HTMLList{

	private $dao;
	private $userName = array();

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

		$detailLink = self::getOrderDetailLink($order->getId());
		$this->addLink("order_id_link", array(
			"text" => $order->getTrackingNumber(),
			"link" => $detailLink
		));

		$this->addLabel("order_date", array(
			"text" => date('Y-m-d H:i', $order->getOrderDate())
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
			"link" => ( (isset($mailStatus["payment"])) ? self::getOrderDetailLink($order->getId()) . "#mail_status" : $this->getOrderMailLink($order->getId())."?type=" . SOYShop_Order::SENDMAIL_TYPE_PAYMENT)
		));

		$this->addLink("confirm_mail_status", array(
			"text" => (isset($mailStatus["confirm"])) ? "済" : "未送信",
			"link" => ( (isset($mailStatus["confirm"])) ? self::getOrderDetailLink($order->getId()) . "#mail_status" : $this->getOrderMailLink($order->getId())."?type=" . SOYShop_Order::SENDMAIL_TYPE_CONFIRM)
		));

		$this->addLink("delivery_mail_status", array(
			"text" => (isset($mailStatus["delivery"])) ? "済" : "未送信",
			"link" => ( (isset($mailStatus["delivery"])) ? self::getOrderDetailLink($order->getId()) . "#mail_status" : $this->getOrderMailLink($order->getId())."?type=" . SOYShop_Order::SENDMAIL_TYPE_DELIVERY)
		));

		$this->addLink("customer_link", array(
			"link" => self::getUserLink() . "/" . $order->getUserId(),
			"text" => self::getUserName($order->getUserId())
		));

		$this->addLabel("order_price", array(
			"text" => number_format($order->getPrice())
		));
	}

	private function getUserName($userId){
		if(!is_numeric($userId)) return "";
		if(isset($this->userName[$userId])) return $this->userName[$userId];
		if(!$this->dao) $this->dao = new SOY2DAO();

		try{
			$res = $this->dao->executeQuery("SELECT name FROM soyshop_user WHERE id = :userId LIMIT 1", array(":userId" => $userId));
			$this->userName[$userId] = (isset($res[0]["name"])) ? $res[0]["name"] : "---";
		}catch(Exception $e){
			$this->userName[$userId] = $e->getMessage();//"---";
		}

		return $this->userName[$userId];
	}

	private function getOrderDetailLink($id = null){
		if(!$this->orderDetailLink) $this->orderDetailLink = SOY2PageController::createLink("Order.Detail");
		return ($id > 0) ? $this->orderDetailLink . "/$id" : $this->orderDetailLink ;
	}

	private function getOrderMailLink($id = null){
		if(!$this->orderMailLink) $this->orderMailLink = SOY2PageController::createLink("Order.Mail");
		return ($id > 0) ? $this->orderMailLink . "/$id" : $this->orderMailLink ;
	}

	private function getUserLink(){
		if(!$this->userLink) $this->userLink = SOY2PageController::createLink("User.Detail");
		return $this->userLink;
	}
}
