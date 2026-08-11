<?php
include(dirname(__FILE__) . "/common.php");

class CommonCustomerCategoryVoiceBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput(WebPage $page){

		$obj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($obj) || get_class($obj) != "SOYShop_Page") return;

		if(
			$obj->getType() == SOYShop_Page::TYPE_COMPLEX ||
			$obj->getType() == SOYShop_Page::TYPE_FREE ||
			$obj->getType() == SOYShop_Page::TYPE_DETAIL ||
			$obj->getType() == SOYShop_Page::TYPE_SEARCH
		) return;

		//商品一覧ページ以外では動作しない
		
		$current = $obj->getObject()->getCurrentCategory();
		if(is_null($current)) return;

		$category = $current;

		$values = soy2_unserialize(soyshop_get_category_attribute_value((int)$category->getId(), "customer_category_voice_plugin", "string"));

		$page->addModel("is_category_voice_list", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (count($values) > 0)
		));

		$page->createAdd("category_voice_list", "CommonCustomerCategoryVoiceList", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"list" => $values
		));
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput","common_customer_category_voice","CommonCustomerCategoryVoiceBeforeOutput");

class CommonCustomerCategoryVoiceList extends HTMLList{

	protected function populateItem($entity) {

		$this->addLabel("name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity["name"]
		));

		$this->addLabel("voice", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => (isset($entity["value"]) && $entity["value"]) ? nl2br($entity["value"]) : ""
		));

	}
}
