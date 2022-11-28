<?php
class CommonFavoriteItemCustomField extends SOYShopItemCustomFieldBase{

	private $favoriteLogic;
	private $checkFavorite;
	private $checkPurchased;

	function onOutput($htmlObj, SOYShop_Item $item){

		$isLoggedInUserId = MyPageLogic::getMyPage()->getUserId();	//1以上の値でマイページにログインしたことになっている
		$isFav = (is_numeric($item->getId()) && $isLoggedInUserId > 0) ? self::_logic()->checkFavorite($item->getId(), $isLoggedInUserId) : false;

		//ログインしていて、まだお気に入り登録していない場合のみボタンを表示する
		$htmlObj->addActionLink("favorite_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "?favorite=" . $item->getId() ."&a=add",
			"onclick" => "return confirm('お気に入りに登録しますか？');",
			"visible" => ($isLoggedInUserId > 0 && !$isFav)
		));

		$htmlObj->addActionLink("favorite_cancel_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "?favorite=" . $item->getId() ."&a=remove",
			"onclick" => "return confirm('お気に入りを解除しますか？');",
			"visible" => ($isLoggedInUserId > 0 && $isFav)
		));


		//お気に入り未登録
		$htmlObj->addModel("no_favorite", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (!$isFav)
		));

		//お気に入り登録済み
		$htmlObj->addModel("is_favorite", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($isFav)
		));

		// cms:id="is_purchased"等はoutput_item.phpに移行
		//購入していない
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "common_favorite_item", "CommonFavoriteItemCustomField");
