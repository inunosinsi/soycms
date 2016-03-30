<?php

class DownloadAssitantOrderStatusUpdate extends SOYShopOrderStatusUpdate{

	private $dao;

	function execute(SOYShop_Order $order){

		$statusLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadStatusLogic");

		$paymentStatus = $order->getPaymentStatus();
		switch($paymentStatus){
			//支払確認済み
			case SOYShop_Order::PAYMENT_STATUS_CONFIRMED:
				$statusLogic->receivedStatus($order->getId());
				break;
			//それ以外
			case SOYShop_Order::PAYMENT_STATUS_WAIT:
			case SOYShop_Order::PAYMENT_STATUS_ERROR:
			default:
				$statusLogic->cancelStatus($order->getId());
				break;
		}
	}
}

SOYShopPlugin::extension("soyshop.order.status.update", "download_assistant", "DownloadAssitantOrderStatusUpdate");
?>