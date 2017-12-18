<?php

class ProfitLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::imports("module.plugins.payment_construction.domain.*");
	}

	function saveListPrice($itemOrderId){
		$itemOrder = self::getItemOrderById($itemOrderId);
		if(is_null($itemOrder->getId())) return false;	//何もしない

		$listPrice = self::getListPriceByItemId($itemOrder->getItemId());
		if($listPrice === 0) return false;	//何もしない

		$dao = self::dao();
		$obj = new SOYShop_ListPriceLog();
		$obj->setItemOrderId($itemOrderId);
		$obj->setListPrice($listPrice);
		try{
			$dao->insert($obj);
		}catch(Exception $e){
			var_dump($e);
			return false;
		}

		return true;
	}

	// 販売価格から単価を引いたものの合算をattributeに記録
	function saveGrossProfit($orderId){
		$itemOrders = self::getItemOrdersByOrderId($orderId);
		if(!count($itemOrders)) return false;

		$order = self::getOrderByOrderId($orderId);
		if(is_null($order->getId())) return false;

		$grossProfit = 0;
		foreach($itemOrders as $itemOrder){
			$price = $itemOrder->getItemPrice();	//販売価格
			$count = $itemOrder->getItemCount();	//個数
			try{
				$listPrice = self::dao()->getByItemOrderId($itemOrder->getId())->getListPrice();
			}catch(Exception $e){
				$listPrice = self::getListPriceByItemId($itemOrder->getItemId());
				if($listPrice === 0) continue;
			}
			$grossProfit += ($price - $listPrice) * $count;
		}

		//粗利を登録する
		$attr = array("name" => "(販売価格 - 定価)の合算", "value" => number_format($grossProfit) . " 円", "hidden" => false, "readonly" => true);
		$order->setAttribute("payment_construction", $attr);

		//includeの項目も引く
		$mods = $order->getModuleList();
		if(count($mods)){
			$includeItemTotal = 0;
			foreach($mods as $modId => $mod){
				if(strpos($modId, "payment_commission_include_") === false) continue;
				$includeItemTotal += (int)$mod->getPrice();
			}
			if($includeItemTotal > 0){
				$attr = array("name" => "上記値から諸経費を引いた合計", "value" => number_format($grossProfit - $includeItemTotal) . " 円", "hidden" => false, "readonly" => true);
				$order->setAttribute("payment_construction_include", $attr);
			}
		}

		try{
			self::orderDao()->update($order);
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	private function getItemOrderById($itemOrderId){
		try{
			return self::itemOrderDao()->getById($itemOrderId);
		}catch(Exception $e){
			return new SOYShop_ItemOrder();
		}
	}

	private function getItemOrdersByOrderId($orderId){
		try{
			return self::itemOrderDao()->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}

	private function getListPriceByItemId($itemId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			return (int)$dao->getById($itemId)->getAttribute("list_price");
		}catch(Exception $e){
			return 0;
		}
	}

	private function getOrderByOrderId($orderId){
		try{
			return self::orderDao()->getById($orderId);
		}catch(Exception $e){
			return new SOYShop_Order();
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_ListPriceLogDAO");
		return $dao;
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
