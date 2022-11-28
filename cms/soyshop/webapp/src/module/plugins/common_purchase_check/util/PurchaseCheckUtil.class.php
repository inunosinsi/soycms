<?php

class PurchaseCheckUtil{

	function __construct(){}

	public static function getConfig(){
		return SOYShop_DataSets::get("common_purchase_check.config", array(
			"paid" => 1
		));
	}

	public static function saveConfig(array $values){
		SOYShop_DataSets::put("common_purchase_check.config", $values);
	}

	public static function checkPurchasedByItemId(int $itemId){
		$isLoggedInUserId = MyPageLogic::getMyPage()->getUserId();
		if($isLoggedInUserId === 0) return false;	//userIdが0の場合は誰もログインしていないので、購入履歴は不明になる

		return SOY2Logic::createInstance("module.plugins.common_purchase_check.logic.PurchasedCheckLogic")->checkPurchased($itemId, $isLoggedInUserId);
	}
}
