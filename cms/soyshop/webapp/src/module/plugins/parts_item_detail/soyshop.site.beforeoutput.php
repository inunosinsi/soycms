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
		$page->createAdd("item_by_alias", "SOYShop_ItemListComponent", array(
			"list" => array(self::getItem($alias)),
			"soy2prefix" => "block"
		));
	}

	private function getItem($alias){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if(!strlen($alias)) return new SOYShop_Item();

		try{
			$item = $dao->getByCode($alias);
		}catch(Exception $e){
			try{
				$item = $dao->getByAlias($alias);
			}catch(Exception $e){
				if(!strpos($alias, ".html")){
					try{
						$item = $dao->getByAlias($alias . ".html");
					}catch(Exception $e){
						$item = new SOYShop_Item();
					}
				}
			}
		}

		if(is_null($item->getId())) return $item;

		//削除されていないか？
		if($item->getIsDisabled() == SOYShop_Item::IS_DISABLED) return new SOYShop_Item();

		//公開されていないか？
		if($item->getIsOpen() == SOYShop_Item::NO_OPEN) return new SOYShop_Item();

		//公開期限外であるか？
		if($item->getOpenPeriodStart() > SOY2_NOW || $item->getOpenPeriodEnd() < SOY2_NOW) return SOYShop_Item();

		return $item;
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "parts_item_detail", "ItemDetailBeforeOutput");
