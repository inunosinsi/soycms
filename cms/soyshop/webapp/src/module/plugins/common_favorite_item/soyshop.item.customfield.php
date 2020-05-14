<?php
class CommonFavoriteItemCustomField extends SOYShopItemCustomFieldBase{

	private $favoriteLogic;
	private $isLoggedIn;
	private $checkFavorite;
	private $checkPurchased;

	function onOutput($htmlObj, SOYShop_Item $item){

		$this->prepare($item->getId());

		//ログインしていて、まだお気に入り登録していない場合のみボタンを表示する
		$htmlObj->addActionLink("favorite_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "?favorite=" . $item->getId() ."&a=add",
			"onclick" => "return confirm('お気に入りに登録しますか？');",
			"visible" => ($this->isLoggedIn && !$this->checkFavorite)
		));

		$htmlObj->addActionLink("favorite_cancel_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "?favorite=" . $item->getId() ."&a=remove",
			"onclick" => "return confirm('お気に入りを解除しますか？');",
			"visible" => ($this->isLoggedIn && $this->checkFavorite)
		));

		//お気に入り未登録
		$htmlObj->addModel("no_favorite", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (!$this->checkFavorite)
		));

		//お気に入り登録済み
		$htmlObj->addModel("is_favorite", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($this->checkFavorite)
		));

		//購入していない
		$htmlObj->addModel("no_purchased", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (!$this->checkPurchased)
		));

		//購入済み
		$htmlObj->addModel("is_purchased", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($this->checkPurchased)
		));
	}

	function prepare($itemId){
		if(!$this->favoriteLogic && isset($itemId)){
			$this->favoriteLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");
			$this->isLoggedIn = $this->favoriteLogic->checkLogin();
			$this->checkFavorite = $this->favoriteLogic->checkFavorite($itemId);
		}

		if($this->isLoggedIn && $this->checkFavorite){
			$this->checkPurchased = $this->favoriteLogic->checkPurchased($itemId);
		}
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "common_favorite_item", "CommonFavoriteItemCustomField");
