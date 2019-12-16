<?php

class FixedPointGrantLogic extends SOY2LogicBase {

	private $cart;

	function __construct(){
		SOY2::import("module.plugins.fixed_point_grant.util.FixedPointGrantUtil");
	}

	function getTotalPointOnCart($orderId = null){
		$itemOrders = $this->cart->getItems();

		//クレジット支払からの結果通知の場合はCartLogicのitemsは消えているので、再度取得する
		if(isset($orderId) && is_null($itemOrders) || !is_array($itemOrders) || !count($itemOrders)){
			try{
				$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($orderId);
			}catch(Exception $e){
				$itemOrders = array();
			}
		}

		if(!count($itemOrders)) return 0;

		$total = 0;
		foreach($itemOrders as $itemOrder){
			$total += self::_getFixedPointByItemId($itemOrder->getItemId()) * $itemOrder->getItemCount();
		}

		return $total;
	}

	function getFixedPointByItemId($itemId){
		return self::_getFixedPointByItemId($itemId);
	}

	private function _getFixedPointByItemId($itemId){
		static $points;
		if(isset($points[$itemId])) return $points[$itemId];

		try{
			$points[$itemId] = (int)self::attrDao()->get($itemId, FixedPointGrantUtil::PLUGIN_ID)->getValue();
		}catch(Exception $e){
			$points[$itemId] = 0;
		}
		return $points[$itemId];
	}

	function getTotalPointByOrderId($orderId){
		SOY2::imports("module.plugins.common_point_base.domain.*");
		try{
			$histories = SOY2DAOFactory::create("SOYShop_PointHistoryDAO")->getByOrderId($orderId);
		}catch(Exception $e){
			$histories = array();
		}

		if(!count($histories)) return 0;

		//ポイントが+の値が合った時に返す
		foreach($histories as $history){
			if((int)$history->getPoint() > 0){
				return $history->getPoint();
			}
		}

		return 0;
	}

	private function attrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
