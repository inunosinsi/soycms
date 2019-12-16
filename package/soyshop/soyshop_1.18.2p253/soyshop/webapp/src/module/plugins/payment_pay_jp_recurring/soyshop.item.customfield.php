<?php
/*
 */
class PayJpRecurringItemCustomField extends SOYShopItemCustomFieldBase{


	function doPost(SOYShop_Item $item){
		$logic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$logic->initPayJp();
		$logic->createPlanTokenByItemId($item->getId());
	}

	function getForm(SOYShop_Item $item){}
	function onOutput($htmlObj, SOYShop_Item $item){}
	function onDelete($id){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "payment_pay_jp_recurring", "PayJpRecurringItemCustomField");
