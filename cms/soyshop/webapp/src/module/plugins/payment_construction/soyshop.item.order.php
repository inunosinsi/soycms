<?php

class PaymentConstructionItemOrder extends SOYShopItemOrderBase{

	function order($itemOrderId){
		self::logic()->saveListPrice($itemOrderId);
	}

	// 販売価格 - 定価の合算を記録しておく
	function complete($orderId){
		self::logic()->saveGrossProfit($orderId);
	}

	function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.payment_construction.logic.ProfitLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.item.order", "payment_construction", "PaymentConstructionItemOrder");
