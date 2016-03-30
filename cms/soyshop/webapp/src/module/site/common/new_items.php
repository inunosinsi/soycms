<?php
SOY2::import("util.SOYShopPluginUtil");
function soyshop_new_items($html, $htmlObj){
	
	$obj = $htmlObj->create("soyshop_new_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_new_items", $html)
	));
	
	$items = array();
	
	if(SOYShopPluginUtil::checkIsActive("common_new_item")){
		$config = SOYShop_DataSets::get("common_new_item", array("count" => 3));
		$count = $config["count"];
			
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$dao->setLimit($count);
			
		try{
			$items = $dao->getByIsOpenOnlyParent(1);
		}catch(Exception $e){
		}
		
		$obj->createAdd("new_item_list", "SOYShop_ItemListComponent", array(
			"list" => $items,
			"soy2prefix" => "block"
		));
	}
	
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