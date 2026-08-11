<?php
class CommonPurchaseCheckCustomField extends SOYShopItemCustomFieldBase{

	function onOutput($htmlObj, SOYShop_Item $item){
		// output_item.phpに移行
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "common_purchase_check", "CommonPurchaseCheckCustomField");
