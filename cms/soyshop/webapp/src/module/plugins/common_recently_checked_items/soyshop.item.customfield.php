<?php
/*
 */
class RecentlyCheckedItemsCustomField extends SOYShopItemCustomFieldBase{

	var $itemDAO;
	const MAX = 50;

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		//カートやマイページの場合も動作させない
		if(!method_exists($htmlObj, "getPageClassName")){
			return;
		}

		//詳細ページのみ
		if($htmlObj->getPageClassName() != "SOYShop_DetailPage"){
			return;
		}

		//今表示している商品
		$lastId = $item->getId();
		if(!$item || !$lastId){
			return;
		}


		$sessionKey = "soyshop_" . SOYSHOP_ID . "_recently_checked_item_ids";

		//設定：最大表示数
		$config = SOYShop_DataSets::get("recently_checked_items", array(
			"max_display_number" => 10,
		));

		//現在の値
		$userSession = SOY2ActionSession::getUserSession();
		$itemIds = $userSession->getAttribute($sessionKey);
		if(!is_array($itemIds)){
			$itemIds = array();
		}


		//すでにある場合は消去
		if(count($itemIds)){
			$itemIds = array_diff($itemIds,array($lastId));
		}

		//多い分は消去
		if(count($itemIds) > $config["max_display_number"]){
			$itemIds = array_slice($itemIds, 0, $config["max_display_number"]);
		}

		//先頭に挿入
		array_unshift($itemIds, $lastId);

		//保存
		$userSession->setAttribute($sessionKey, $itemIds);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_recently_checked_items","RecentlyCheckedItemsCustomField");
