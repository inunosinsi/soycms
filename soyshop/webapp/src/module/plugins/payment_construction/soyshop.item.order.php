<?php

class PaymentConstructionItemOrder extends SOYShopItemOrderBase{

	function update(SOYShop_ItemOrder $itemOrder){
		SOY2::import("module.plugins.payment_construction.util.PaymentConstructionUtil");
		if(PaymentConstructionUtil::isItemStockAutoInsert()){
			//入力した商品個数
			$itemCount = (int)$itemOrder->getItemCount();
			$item = self::getItemById($itemOrder->getItemId());
			if(is_null($item->getId())) return;

			$oldStock = (int)$item->getStock();
			if($oldStock < $itemCount){
				$item->setStock($itemCount);
				try{
					self::itemDao()->update($item);
				}catch(Exception $e){
					return;
				}

				//在庫数変更履歴プラグインを併用している場合
				SOY2::import("util.SOYShopPluginUtil");
				if(SOYShopPluginUtil::checkIsActive("item_stock_manager")){
					$dao = self::stockDao();
					$obj = new SOYShop_StockHistory();
					$obj->setItemId($itemOrder->getItemId());
					$obj->setUpdateStock($itemCount - $oldStock);
					$obj->setMemo("在庫数を" . $oldStock . "から" . $itemCount . "に変更しました");

					try{
						$dao->insert($obj);
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}
		}
	}

	function edit(SOYShop_ItemOrder $itemOrder){
		SOY2::import("module.plugins.payment_construction.util.PaymentConstructionUtil");
		if(PaymentConstructionUtil::isItemStockAutoInsert()){
			//在庫数が0より少ない場合は自動で追加
			$item = self::getItemById($itemOrder->getItemId());
			if($item->getStock() < 0){
				$oldStock = $item->getStock();
				$item->setStock(0);
				try{
					self::itemDao()->update($item);
				}catch(Exception $e){
					return;
				}

				//在庫数変更履歴プラグインを併用している場合
				SOY2::import("util.SOYShopPluginUtil");
				if(SOYShopPluginUtil::checkIsActive("item_stock_manager")){
					$dao = self::stockDao();
					$obj = new SOYShop_StockHistory();
					$obj->setItemId($itemOrder->getItemId());
					$obj->setUpdateStock(-1 * $oldStock);
					$obj->setMemo("在庫数を" . $oldStock . "から0に変更しました");

					try{
						$dao->insert($obj);
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}
		}
	}

	function order($itemOrderId){
		self::logic()->saveListPrice($itemOrderId);
	}

	// 販売価格 - 定価の合算を記録しておく
	function complete($orderId){
		self::logic()->saveGrossProfit($orderId);
	}

	private function getItemById($itemId){
		try{
			return self::itemDao()->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private function itemDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}

	private function stockDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::imports("module.plugins.item_stock_manager.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");
		}
		return $dao;
	}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.payment_construction.logic.ProfitLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.item.order", "payment_construction", "PaymentConstructionItemOrder");
