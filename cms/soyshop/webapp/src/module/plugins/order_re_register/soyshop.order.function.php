<?php

class OrderReRegisterFunction extends SOYShopOrderFunction{

	/**
	 * title text
	 */
	function getTitle(){
		return "注文の再登録";
	}

	function getDialogMessage(){
		return "注文を再登録しますか？";
	}

	/**
	 * @return html
	 */
	function getPage(){
		try{
			$itemOrders = self::itemOrderDao()->getByOrderId($this->getOrderId());
		}catch(Exception $e){
			$itemOrders = array();
		}

		if(count($itemOrders)){
			include_once(SOYSHOP_WEBAPP . "pages/Order/Register/common.php");
			$cart = AdminCartLogic::getCart();
			foreach($itemOrders as $itemOrder){
				$cart->addItem($itemOrder->getItemId(), $itemOrder->getItemCount());
			}
			$cart->save();
		}

		SOY2PageController::jump("Order.Register");
	}

	private function itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "order_re_register", "OrderReRegisterFunction");
