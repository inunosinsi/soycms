<?php
function soyshop_auto_ranking($html, $page){
		
	$obj = $page->create("soyshop_auto_ranking", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_auto_ranking", $html)
	));
	
	$displayLogic = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.DisplayRankingLogic");
	$items = $displayLogic->getItems();
	
	SOY2::import("base.site.classes.SOYShop_ItemListComponent");
	$obj->createAdd("ranking_item_list", "SOYShop_ItemListComponent", array(
		"soy2prefix" => "block",
		"list" => $items
	));
		
	$obj->display();
}
?>