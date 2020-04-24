<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

SOY2::import("module.site.common.output_item",".php");

class ItemDetailBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		$alias = null;

		//カートページとマイページでは読み込まない
		$pageObj = $page->getPageObject();
		if(is_object($pageObj) && get_class($pageObj) == "SOYShop_Page"){
			$pageType = $pageObj->getType();
			//商品一覧ページと商品詳細ページでは表示しない
			if($pageType != SOYShop_Page::TYPE_LIST && $pageType != SOYShop_Page::TYPE_DETAIL){
				$args = $page->getArguments();
				if(isset($args[0])) $alias = trim($args[0]);
			}
		}

		//item
		SOY2::import("module.plugins.parts_item_detail.util.PartsItemDetailUtil");
		$page->createAdd("item_by_alias", "SOYShop_ItemListComponent", array(
			"list" => array(PartsItemDetailUtil::getItemByAlias($alias)),
			"soy2prefix" => "block"
		));
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "parts_item_detail", "ItemDetailBeforeOutput");
