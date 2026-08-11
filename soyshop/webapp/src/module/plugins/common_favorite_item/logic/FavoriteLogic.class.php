<?php

class FavoriteLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.common_favorite_item.domain.SOYShop_FavoriteItemDAO");
	}

	//お気に入りに登録する
	function register(int $itemId, int $userId){
		//すでにお気に入りに登録していないかをチェックする。すでに登録されている場合は処理を終了する
		if(self::_check($itemId, $userId)) return;

		//ここから登録を開始する
		$fav = new SOYShop_FavoriteItem();
		$fav->setItemId($itemId);
		$fav->setUserId($userId);

		try{
			self::_dao()->insert($fav);
		}catch(Exception $e){
			//
		}
	}

	//お気に入りを解除する
	function cancel(int $itemId, int $userId){
		try{
			self::_dao()->deleteByItemIdAndUserId($itemId, $userId);
		}catch(Exception $e){
			//
		}
	}

	//お気に入り商品の情報を更新する
	function update(int $itemId, int $userId){
		$fav = self::_get($itemId, $userId);
		$fav->setPurchased(SOYShop_FavoriteItem::PURCHASED);
		try{
			self::_dao()->update($fav);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * 登録されている商品を選択しているか。登録されていればtrue
	 * @param itemId
	 * @return boolean
	 */
	function checkItem(int $itemId){
		return (strlen((string)soyshop_get_item_object($itemId)->getName()));
	}


	/**
	 * お気に入りに登録済みか調べる。既に登録済みの場合はtrue
	 * @param itemId, userId
	 * @return boolean
	 */
	function checkFavorite(int $itemId, int $userId=0){
		return self::_check($itemId, $userId);
	}

	function _check(int $itemId, int $userId=0){
		return (is_numeric(self::_get($itemId, $userId)->getId()));
	}

	/**
	 * 購入済みフラグが立っていたらtrue
	 * @param itemId, userId
	 * @return boolean
	 */
	function checkPurchased(int $itemId, int $userId=0){
		return (self::_get($itemId, $userId)->getPurchased());
	}

	function getUsersByFavoriteItemId(int $itemId){
		return self::_dao()->getUsersByFavoriteItemId($itemId);
	}

	function getFavoriteItem(int $itemId, int $userId=0){
		return self::_get($itemId, $userId);
	}

	private function _get(int $itemId, int $userId=0){
		try{
			return self::_dao()->getByItemIdAndUserId($itemId, $userId);
		}catch(Exception $e){
			return new SOYShop_FavoriteItem();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_FavoriteItemDAO");
		return $dao;
	}
}
