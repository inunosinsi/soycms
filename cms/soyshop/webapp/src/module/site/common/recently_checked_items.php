<?php
function soyshop_recently_checked_items($html,$htmlObj){

	//表示しているページが商品詳細ページかどうか
	$isDetailPage = get_class($htmlObj->getPageObject()->getPageObject()) == "SOYShop_DetailPage";

	//保存している値を取得
	$sessionKey = "soyshop_" . SOYSHOP_ID . "_recently_checked_item_ids";
	$userSession = SOY2ActionSession::getUserSession();
	$itemIds = $userSession->getAttribute($sessionKey);

	//無ければ何もしない
	if(!is_array($itemIds) || !count($itemIds)){
		//終了
		return;
	}

	//詳細ページであればいま表示している商品を除外する
	if($isDetailPage){
		array_shift($itemIds);
	}

	//設定：最大表示数
	$config = SOYShop_DataSets::get("recently_checked_items", array(
		"max_display_number" => 10,
	));

	//商品情報を取得
	$items = array();
	$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	foreach($itemIds as $value){
		try{
			$item = $dao->getById($value);
			if($item->isPublished()){
				$items[$item->getId()] = $item;
			}
		}catch(Exception $e){

		}

		//表示数に達したら終了
		if(count($items) >= $config["max_display_number"]){
			break;
		}
	}


	//argumentsの1つ目にpageIdを付けてキャッシュファイルが異なるようにすべき？（現状キャッシュファイルは使われない）
	//$pageId = strtr($htmlObj->getPageObject()->getPageObject()->getPage()->getUri(),array("/","_"));
	$obj = $htmlObj->create("soyshop_recently_checked_items","HTMLTemplatePage", array(
		"arguments" => array("soyshop_recently_checked_items",$html)
	));

	$obj->createAdd("recently_checked_item_list","SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => "block",
	));

	//商品があるときだけ表示
	if(count($items) > 0){
		$obj->display();
	}
}
