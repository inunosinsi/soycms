<?php

class OrderCopyFunction extends SOYShopOrderFunction{

	/**
	 * title text
	 */
	function getTitle(){
		return "注文の複製";
	}

	function getDialogMessage(){
		return "注文を複製しますか？";
	}

	/**
	 * @return html
	 */
	function getPage(){
		$orderId = $this->getOrderId();
		$order = self::getOrderById($orderId);
		if(is_null($order->getId())) return;	//@ToDo エラー
		$order->setId(null);
		$order->setOrderDate(time());

		//注文番号を生成
		$trackingNumber = SOY2Logic::createInstance("logic.order.OrderLogic")->getTrackingNumber($order);
		$order->setTrackingNumber($trackingNumber);

		try{
			$newOrderId = self::orderDao()->insert($order);
		}catch(Exception $e){
			return;	//エラー
		}

		try{
			$itemOrders = self::itemOrderDao()->getByOrderId($orderId);
		}catch(Exception $e){
			return;	//エラー
		}

		SOYShopPlugin::load("soyshop.item.order");
		foreach($itemOrders as $itemOrder){
			$itemOrder->setOrderId($newOrderId);
			try{
				$itemOrderId = self::itemOrderDao()->insert($itemOrder);
			}catch(Exception $e){
				var_dump($e);
			}

			//soyshop.item.orderの拡張ポイント
			SOYShopPlugin::invoke("soyshop.item.order", array(
				"mode" => "order",
				"itemOrderId" => $itemOrderId
			));
		}

		SOY2PageController::jump("Order.Detail." . $newOrderId . "?copy");
	}

	private function getOrderById($orderId){
		try{
			return self::orderDao()->getById($orderId);
		}catch(Exception $e){
			return new SOYShop_Order();
		}
	}

	private function orderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		return $dao;
	}

	private function itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "order_copy", "OrderCopyFunction");
