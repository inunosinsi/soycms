<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

SOY2::import("module.site.common.output_item",".php");

class ItemDetailBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		$pageObj = $page->getPageObject();
		
		//カートページとマイページでは読み込まない
		if(get_class($pageObj) != "SOYShop_Page"){
			return;
		}
		
		$pageType = $pageObj->getType();
		if($pageType == SOYShop_Page::TYPE_LIST || $pageType == SOYShop_Page::TYPE_DETAIL){
			return;
		}
		
		$args = $page->getArguments();
		if(count($args) == 0) return;
			
		$alias = $args[0];
		
//		if(strpos($alias,".html")==false){
//			$alias = $alias.".html";
//		}
			
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$item = $dao->getByAlias($alias);
		}catch(Exception $e){
			return;
		}
			
		//item
		$page->createAdd("item_by_alias", "SOYShop_ItemListComponent", array(
			"list" => array($item),
			"soy2prefix" => "block"
		));
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput","parts_item_detail","ItemDetailBeforeOutput");