<?php

class CommonItemStockHistory extends SOYShopItemUpdateBase{

	function addHistory(SOYShop_Item $item, $oldStock){

		$newStock = (int)$_POST["Item"]["stock"];
		if($oldStock != $newStock){
			$logMessage = "在庫数を" . $oldStock."から" . $newStock."に変更しました";

			SOY2::imports("module.plugins.item_stock_manager.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");

			$obj = new SOYShop_StockHistory();
			$obj->setItemId($item->getId());
			$obj->setMemo($logMessage);

			try{
				$dao->insert($obj);
			}catch(Exception $e){
				var_dump($e);
			}

		}
	}

	function display(SOYShop_Item $item){
		SOY2::imports("module.plugins.item_stock_manager.domain.*");
		$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");
		$dao->setLimit(5);

		try{
			return $dao->getByItemId($item->getId());
		}catch(Exception $e){
			return array();
		}
	}
}

SOYShopPlugin::extension("soyshop.item.update","common_stock_history","CommonItemStockHistory");
