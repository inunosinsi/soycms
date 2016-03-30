<?php
function soyshop_relative_items($html, $htmlObj){

	$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

	//詳細ページを開いている時
	if(method_exists($htmlObj, "getItem")){
		$item = $htmlObj->getItem();
	//商品詳細表示プラグインでも関連商品を取得できるようにした
	}else{
		$alias = substr($_SERVER["REDIRECT_URL"], strrpos($_SERVER["REDIRECT_URL"], "/") + 1);
		try{
			$item = $itemDao->getByAlias($alias);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
	}

	try{
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attr = $dao->get($item->getId(), "_relative_items");
		$values = soy2_unserialize($attr->getValue());
		if(!is_array($values)) $values = array();
	}catch(Exception $e){
		$values = array();
	}
	
	$items = array();
	foreach($values as $key => $value){
		try{
			$item = $itemDao->getByCode($value);
			if($item->isPublished()){
				$items[$item->getId()] = $item;
			}
		}catch(Exception $e){
			//
		}
	}

	$obj = $htmlObj->create("soyshop_relative_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_relative_items", $html)
	));

	$obj->createAdd("relative_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,//cms:idは互換性維持のため残しておく
	));
	$obj->createAdd("relative_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => "block",
	));

	//商品があるときだけ表示
	if(count($items) > 0){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
?>