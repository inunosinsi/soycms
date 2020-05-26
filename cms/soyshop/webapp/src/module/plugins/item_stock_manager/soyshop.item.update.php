<?php
class ItemStockManagerUpdate extends SOYShopItemUpdateBase{

	function addHistory(SOYShop_Item $item, $oldStock){

		$newStock = (isset($_POST["Item"]["stock"])) ? (int)$_POST["Item"]["stock"] : (int)$item->getStock();
		if($oldStock != $newStock){
			$logMessage = "在庫数を" . $oldStock."から" . $newStock . "に変更しました";

			SOY2::import("module.plugins.item_stock_manager.domain.SOYShop_StockHistoryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");

			$obj = new SOYShop_StockHistory();
			$obj->setItemId($item->getId());
			$obj->setUpdateStock($newStock - $oldStock);
			$obj->setMemo($logMessage);

			try{
				$dao->insert($obj);
			}catch(Exception $e){
				var_dump($e);
				//
			}
		}
	}

	function display(SOYShop_Item $item){
		SOY2::import("module.plugins.item_stock_manager.domain.SOYShop_StockHistoryDAO");
		$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");
		$dao->setLimit(5);

		try{
			return $dao->getByItemId($item->getId());
		}catch(Exception $e){
			return array();
		}
	}
}

SOYShopPlugin::extension("soyshop.item.update","item_stock_manager","ItemStockManagerUpdate");
