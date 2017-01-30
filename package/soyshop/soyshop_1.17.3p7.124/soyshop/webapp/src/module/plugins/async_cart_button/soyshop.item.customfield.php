<?php
/*
 */
class AsyncCartButtonCustomField extends SOYShopItemCustomFieldBase{

	private $attrDao;

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$htmlObj->addForm("async_cart_form", array(
		"method" => "post",
		"action" => soyshop_get_cart_url(true) . "?a=add&count=1&item=" . $item->getId(),
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"id" => "soyshop_async_cart_" . $item->getId()
	));
		
		$htmlObj->addSelect("async_cart_select", array(
			"name" => "count",
			"options" => range(1,10),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"id" => "soyshop_async_count_" . $item->getId()
		));
		
		$htmlObj->addInput("async_cart_input", array(
			"type" => "number",
			"name" => "count",
			"value" => 1,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"id" => "soyshop_async_count_" . $item->getId()
		));
		
		$htmlObj->addLink("async_cart_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "javascript:void(0);",
			"onclick" => "AsyncCartButton.addItem(this," . $item->getId() . "," . $item->getSellingPrice() . ");"
		));
	
		$htmlObj->addModel("async_cart_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"onclick" => "AsyncCartButton.addItem(this," . $item->getId() . "," . $item->getSellingPrice() . ");"
		));
		
		$htmlObj->addInput("standard_price_helper", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"type" => "hidden",
			"value" => self::getStandardFirstPrice($item),
			"attr:id" => "standard_price_helper_" . $item->getId()
		));
	}
	
	private function getStandardFirstPrice(SOYShop_Item $item){
		if($item->getType() != SOYShop_Item::TYPE_GROUP) return $item->getSellingPrice();
		
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("item_standard")) return $item->getSellingPrice();
		
		$keys = self::get($item->getId());
		
		if(!count($keys)) return $item->getSellingPrice();
		
		$child = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic")->getChildItem($item->getId(), $keys);
		return $child->getSellingPrice();
	}
	
	private function get($itemId){
		if(!$this->attrDao) $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$atrs = $this->attrDao->getByItemId($itemId);
		}catch(Exception $e){
			return array();
		}
		
		if(!count($atrs)) return array();
		
		//規格の順を調べる
		$keys = array();
		
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");		
		foreach(ItemStandardUtil::getConfig() as $conf){
			if(!isset($atrs["item_standard_plugin_" . $conf["id"]])) continue;
			
			$vals = explode("\n", $atrs["item_standard_plugin_" . $conf["id"]]->getValue());
			if(isset($vals[0])) $keys[] = trim($vals[0]);
		}
		
		return $keys;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "async_cart_button", "AsyncCartButtonCustomField");
?>