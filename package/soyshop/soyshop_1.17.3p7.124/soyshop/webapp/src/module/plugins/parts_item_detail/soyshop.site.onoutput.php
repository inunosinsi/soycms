<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class PartsItemDetailOnOutput extends SOYShopSiteOnOutputAction{


	/**
	 * @return string
	 */
	function onOutput($html){
	
		$pageType = $this->getPage()->getPageObject()->getType();
		if($pageType == SOYShop_Page::TYPE_FREE || $pageType == SOYShop_Page::TYPE_COMPLEX){
			$args = $this->getPage()->getArguments();
			if(isset($args[0])){
				$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				try{
					$item = $itemDao->getByAlias($args[0]);
				}catch(Exception $e){
					$item = new SOYShop_Item();
				}
				$html = str_replace("%ITEM_NAME%", $item->getName(), $html);
			}
		}
	
		return $html;
	}
	
}

SOYShopPlugin::extension("soyshop.site.onoutput", "parts_item_detail", "PartsItemDetailOnOutput");