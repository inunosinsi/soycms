<?php
if(!class_exists("SOYShop_StockHistoryDAO")){
	include(dirname(__FILE__) . "/domain/SOYShop_StockHistory.class.php");
	include(dirname(__FILE__) . "/domain/SOYShop_StockHistoryDAO.class.php");
}
class CommonItemStockHistory extends SOYShopItemUpdateBase{

	function addHistory(SOYShop_Item $item, $oldStock){
		
		$newStock = (int)$_POST["Item"]["stock"];
		if($oldStock != $newStock){
			$logMessage = "在庫数を" . $oldStock."から" . $newStock."に変更しました";
			
			$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");
			
			$obj = new SOYShop_StockHistory();
			$obj->setItemId($item->getId());
			$obj->setMemo($logMessage);
			$obj->setCreateDate(time());
			
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				var_dump($e);
			}
			
		}
	}
	
	function display(SOYShop_Item $item){
		$dao = SOY2DAOFactory::create("SOYShop_StockHistoryDAO");
		$dao->setLimit(5);
		
		try{
			$history = $dao->getByItemId($item->getId());
		}catch(Exception $e){
			$history = array();
		}
		
		return $history;
	}
}

SOYShopPlugin::extension("soyshop.item.update","common_stock_history","CommonItemStockHistory");
?>