<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class PartsItemDetailOnOutput extends SOYShopSiteOnOutputAction{


    /**
     * @return string
     */
    function onOutput(string $html){

        $pageType = $this->getPage()->getPageObject()->getType();
        if($pageType != SOYShop_Page::TYPE_FREE && $pageType != SOYShop_Page::TYPE_COMPLEX) return $html;

		$args = $this->getPage()->getArguments();
        if(!isset($args[0])) return $html;

		try{
            $item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByAlias($args[0]);
        }catch(Exception $e){
            $item = new SOYShop_Item();
        }
        return str_replace("%ITEM_NAME%", $item->getOpenItemName(), $html);
    }
}

SOYShopPlugin::extension("soyshop.site.onoutput", "parts_item_detail", "PartsItemDetailOnOutput");
