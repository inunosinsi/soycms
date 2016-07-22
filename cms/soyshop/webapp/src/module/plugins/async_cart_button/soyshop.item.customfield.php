<?php
/*
 */
class AsyncCartButtonCustomField extends SOYShopItemCustomFieldBase{

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$htmlObj->addSelect("async_cart_select", array(
			"name" => "count",
			"options" => range(1,10),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"id" => "soyshop_async_count_" . $item->getId()
		));
		
		$htmlObj->addLink("async_cart_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "javascript:void(0);",
			"onclick" => "AsyncCartButton.addItem(this," . $item->getId() . "," . $item->getPrice() . ");"
		));
	
		$htmlObj->addModel("async_cart_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"onclick" => "AsyncCartButton.addItem(this," . $item->getId() . "," . $item->getPrice() . ");"
		));
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "async_cart_button", "AsyncCartButtonCustomField");
?>