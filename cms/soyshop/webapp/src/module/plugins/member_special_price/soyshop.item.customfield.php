<?php
class MemberSpecialPriceItemCustomField extends SOYShopItemCustomFieldBase{

	function onOutput($htmlObj, SOYShop_Item $item){

		$htmlObj->addLabel("member_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format(SOY2Logic::createInstance("module.plugins.member_special_price.logic.SpecialPriceLogic")->getSellingPrice($item))
		));
	}

	function onDelete($id){}
}
SOYShopPlugin::extension("soyshop.item.customfield", "common_favorite_item", "MemberSpecialPriceItemCustomField");
