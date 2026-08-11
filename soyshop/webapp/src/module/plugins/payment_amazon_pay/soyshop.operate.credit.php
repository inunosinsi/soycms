<?php

class AmazonPayOperateCredit extends SOYShopOperateCreditBase{

	function doPostOnOrderDetailPage(SOYShop_Order $order){
		$orderReferenceId = self::_getAttrValue($order, "order_reference_id");
		$amazonAuthorizationId = self::_getAttrValue($order, "amazon_authorization_id");

		if(isset($orderReferenceId) && isset($amazonAuthorizationId)){
			// @ToDo 返金

			// キャンセル
			if($order->getStatus() == SOYShop_Order::ORDER_STATUS_CANCELED){
				if(SOY2Logic::createInstance("module.plugins.payment_amazon_pay.logic.AmazonPayLogic")->cancel($orderReferenceId, $amazonAuthorizationId)){
					self::_insertHistory($order->getId(), "Amazon Pay ワンタイムペイメントでキャンセル処理を行いました。");
				}
			}
		}
	}

	function doPostOnUserDetailPage(SOYShop_User $user){}

	function getFormOnOrderDetailPageTitle(SOYShop_Order $order){
		if(array_key_exists("payment_amazon_pay", $order->getModuleList())){
			return "Amazon Pay";
		}else{
			return null;
		}
	}

	function getFormOnOrderDetailPageContent(SOYShop_Order $order){
		if(array_key_exists("payment_amazon_pay", $order->getModuleList())){
			$orderReferenceId = self::_getAttrValue($order, "order_reference_id");
			$amazonAuthorizationId = self::_getAttrValue($order, "amazon_authorization_id");

			if(isset($orderReferenceId) && isset($amazonAuthorizationId)){
				SOY2::import("module.plugins.payment_amazon_pay.operate.AmazonPayOperatePage");
				$form = SOY2HTMLFactory::createInstance("AmazonPayOperatePage");
				$form->setOrder($order);
				$form->execute();
				return $form->getObject();
			}
		}
	}

	function getFormOnUserDetailPageTitle(SOYShop_User $user){}
	function getFormOnUserDetailPageContent(SOYShop_User $user){}

	private function _getAttrValue(SOYShop_Order $order, $key){
		$attr = $order->getAttribute("payment_amazon_pay." . $key);
		return (isset($attr["value"])) ? $attr["value"] : null;
	}

	/**
	 * 変更履歴をSOY Shop側でも持っておく
	 */
	private function _insertHistory($orderId, $content){
		$dao = self::_dao();
		$history = new SOYShop_OrderStateHistory();
		$history->setOrderId($orderId);
		$history->setAuthor(SOY2Logic::createInstance("logic.order.OrderHistoryLogic")->getAuthor());
		$history->setContent($content);
		$history->setDate(time());

		try{
			$dao->insert($history);
		}catch(Exception $e){
			//
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.operate.credit", "payment_amazon_pay", "AmazonPayOperateCredit");
