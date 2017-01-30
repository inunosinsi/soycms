<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class ItemListCategoryCustomfieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

	const MODULE_ID = "item_list_category_customfield";

	function beforeOutput($page){

		$obj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(get_class($obj) != "SOYShop_Page"){
			return;
		}

		//商品一覧ページ以外では動作しない
		if($obj->getType() == SOYShop_Page::TYPE_COMPLEX || $obj->getType() == SOYShop_Page::TYPE_FREE || $obj->getType() == SOYShop_Page::TYPE_SEARCH){
			return;
		}

		$fieldId = "";

		switch($obj->getType()){
			case SOYShop_Page::TYPE_LIST:
				$pageId = $obj->getId();
			
				if(isset($pageId)){
					SOY2::imports("module.plugins.item_list_category_customfield.util.*");
					$pageConfig = ItemListCategoryCustomfieldUtil::getPageConfig(self::MODULE_ID, $pageId);
		
					$fieldId = (isset($pageConfig["fieldId"])) ? $pageConfig["fieldId"] : null;
				}
				break;
			case SOYShop_Page::TYPE_DETAIL:
				$itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
				try{
					$attrObj = $itemAttributeDao->get($page->getItem()->getId(), self::MODULE_ID);
				}catch(Exception $e){
					$attrObj = new SOYShop_ItemAttribute();
				}
				$fieldId = $attrObj->getValue();
				break;
		}
		
		$fieldLabel = "";
		if($fieldId){
			$attributeDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
			$configs = SOYShop_CategoryAttributeConfig::load();
			foreach($configs as $config){
				if($config->getFieldId() == $fieldId){
					$fieldLabel = $config->getLabel();
					break;
				}
			}
		}
		
		/**
		 * @ToDo パンくずのリンクの出力
		 */
		
		$page->addLabel("current_category_customfield_label", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $fieldLabel
		));
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput","item_list_category_customfield","ItemListCategoryCustomfieldBeforeOutput");