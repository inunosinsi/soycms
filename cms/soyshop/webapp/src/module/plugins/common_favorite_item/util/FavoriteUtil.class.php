<?php

class FavoriteUtil {

	public static function checkPurchasedByItemId(int $itemId){
		$isLoggedInUserId = MyPageLogic::getMyPage()->getUserId();
		if($isLoggedInUserId === 0) return false;	//userIdが0の場合は誰もログインしていないので、購入履歴は不明になる

		return SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic")->checkPurchased($itemId, $isLoggedInUserId);
	}
}
