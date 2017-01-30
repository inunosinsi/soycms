<?php
/*
 */
class CommonNoticeStock extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
	}

	function getForm(SOYShop_Item $item){
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$visible = false;
		
		SOY2::import("module.plugins.common_notice_stock.common.CommonNoticeStockCommon");
		$config = CommonNoticeStockCommon::getConfig();
		$stock = $item->getStock();
		
		//在庫数残りわずか
		if($stock < $config["stock"] && $stock> 0){
			$visible = true;
			$text = nl2br(str_replace("##COUNT##", $stock, $config["notice_stock"]));
		//在庫切れ(一応在庫無視モードも考慮)
		}elseif($stock<1){
			$visible = true;
			$text = nl2br($config["no_stock"]);
		//念の為、通常時の文言
		}else{
			$text = $text = nl2br(str_replace("##COUNT##", $stock, $config["has_stock"]));
		}
		
				
		$htmlObj->addModel("notice_stock", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => $visible
		));
		
		$htmlObj->addLabel("notice_stock_text", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $text
		));
	}

	function onDelete($id){
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_notice_stock","CommonNoticeStock");
?>