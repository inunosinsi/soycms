<?php

class CommonPointGrantCustomField extends SOYShopItemCustomFieldBase{
	
	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$htmlObj->addLabel("item_point_grant_percentage", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => SOY2Logic::createInstance("module.plugins.common_point_grant.logic.PointGrantLogic")->getPercentage($item)
		));
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_point_grant", "CommonPointGrantCustomField");
?>