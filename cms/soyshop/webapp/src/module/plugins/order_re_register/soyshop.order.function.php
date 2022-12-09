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
		include_once(SOYSHOP_WEBAPP . "pages/Order/Register/common.php");
		$cart = AdminCartLogic::getCart();

		try{
			$itemOrders = self::itemOrderDao()->getByOrderId($this->getOrderId());
		}catch(Exception $e){
			$itemOrders = array();
		}

		$doSave = false;
		if(count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				if($itemOrder->getItemId() > 0){
					$cart->addItem($itemOrder->getItemId(), $itemOrder->getItemCount());
				}else{	//登録外商品の場合
					$cart->addUnlistedItem($itemOrder->getItemName(), $itemOrder->getItemCount(), $itemOrder->getItemPrice());
				}
				$doSave = true;
			}
		}

		//顧客情報の登録
		SOY2::import("module.plugins.order_re_register.util.ReRegiserUtil");
		$config = ReRegiserUtil::getConfig();
		if(isset($config["customer"]) && $config["customer"] == 1){
			try{
				$userId = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($this->getOrderId())->getUserId();
			}catch(Exception $e){
				$userId = null;
			}

			if(isset($userId)){
				try{
					$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($userId);
					$cart->setCustomerInformation($user);
					$doSave = true;
				}catch(Exception $e){
					//
				}
			}
		}

		if($doSave) $cart->save();

		SOY2PageController::jump("Order.Register");
	}

	private function itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "order_re_register", "OrderReRegisterFunction");
