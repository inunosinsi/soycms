<?php

class OrderHistoryLogic extends SOY2LogicBase{

	/**
	 * 注文履歴に追加
	 */
	public static function add(int $id, string $content, string $more=""){		
		if(!strlen($more)) $more = null;

		$dao = soyshop_get_hash_table_dao("order_state_history");
		$history = new SOYShop_OrderStateHistory();
		$history->setOrderId($id);
		$history->setContent($content);
		$history->setMore($more);
		$history->setAuthor(self::_getAuthor());
		$dao->insert($history);
	}

	/**
	 * authorを取得する
	 */
	public static function getAuthor(){
		return self::_getAuthor();
	}

	private static function _getAuthor(){
		$session = SOY2ActionSession::getUserSession();
		if(!is_null($session->getAttribute("loginid"))){
			return $session->getAttribute("loginid");
		}else{
			SOY2::import("domain.config.SOYShop_ShopConfig");
			return SOYShop_ShopConfig::load()->getAutoOperateAuthorId();
		}
	}

	/**
	 * 注文状態を変更する
	 */
	public static function changeOrderStatus(SOYShop_Order $order){
		self::add($order->getId(), "注文状態を<strong>「" . $order->getOrderStatusText() ."」</strong>に変更しました。");
	}

	/**
	 * 支払状態を変更する
	 */
	public static function changePaymentStatus(SOYShop_Order $order){
		self::add($order->getId(), "支払い状態を<strong>「" . $order->getPaymentStatusText() ."」</strong>に変更しました。");
	}

}
