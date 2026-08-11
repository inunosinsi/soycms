<?php
function soyshop_auto_ranking(string $html, HTMLPage $page){
		
	$obj = $page->create("soyshop_auto_ranking", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_auto_ranking", $html)
	));

	// cms:customfield="off" の場合はカスタムフィールドの拡張ポイントを実行しない
	$isCallCustomfield = true;
	if(preg_match('/cms:customfield=\"(.*)\"/', $html, $tmp)){
		if(isset($tmp[1]) && $tmp[1] == "off") $isCallCustomfield = false;
	}
	
	$items = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.DisplayRankingLogic")->getItems();
	
	SOY2::import("base.site.classes.SOYShop_ItemListComponent");
	$obj->createAdd("ranking_item_list", "SOYShop_ItemListComponent", array(
		"soy2prefix" => "block",
		"list" => $items,
		"isCallCustomfield" => $isCallCustomfield
	));
		
	$obj->display();
}
