<?php

class FavoriteLogic extends SOY2LogicBase{
	
	private $favoriteDao;
	
	function FavoriteLogic(){
		SOY2::imports("module.plugins.common_favorite_item.domain.*");
		$this->favoriteDao = SOY2DAOFactory::create("SOYShop_FavoriteItemDAO");
	}
	
	//お気に入りに登録する
	function registerFavorite($itemId, $userId){
		
		//すでにお気に入りに登録していないかをチェックする。すでに登録されている場合は処理を終了する
		if($this->checkFavorite($itemId, $userId)) return;
		
		//ここから登録を開始する
		$obj = new SOYShop_FavoriteItem();
		$obj->setItemId($itemId);
		$obj->setUserId($userId);
		
		try{
			$this->favoriteDao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
	
	//お気に入りを解除する
	function cancelFavorite($itemId, $userId){
		try{
			$this->favoriteDao->deleteByItemIdAndUserId($itemId, $userId);
		}catch(Exception $e){
			//
		}
	}
	
	//お気に入り商品の情報を更新する
	function updateFavorite($itemId, $userId){
		$favoriteItem = $this->getFavoriteItem($itemId, $userId);
		
		$favoriteItem->setPurchased(SOYShop_FavoriteItem::PURCHASED);
		try{
			$this->favoriteDao->update($favoriteItem);
		}catch(Exception $e){
			//
		}
	}
	
	/**
	 * @return userId
	 */
	function getUserId(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getUserId();
	}
	
	/**
	 * 登録されている商品を選択しているか。登録されていればtrue
	 * @param itemId
	 * @return boolean
	 */
	function checkItem($itemId){
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$item = $itemDao->getById($itemId);
		}catch(Exception $e){
			return false;
		}
		
		return (strlen($item->getName()));
	}
	
	/**
	 * ログインしていればtrue
	 * return boolean
	 */
	function checkLogin(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getIsLoggedIn();
	}
	
	/**
	 * お気に入りに登録済みか調べる。既に登録済みの場合はtrue
	 * @param itemId, userId
	 * @return boolean
	 */
	function checkFavorite($itemId, $userId = null){
		
		$favoriteItem = $this->getFavoriteItem($itemId, $userId);
		return (!is_null($favoriteItem->getId()));
	}
	
	/**
	 * 購入済みフラグが立っていたらtrue
	 * @param itemId, userId
	 * @return boolean
	 */
	function checkPurchased($itemId, $userId = null){
		$favoriteItem = $this->getFavoriteItem($itemId, $userId);
		return ($favoriteItem->getPurchased());
	}
	
	function getUsersByFavoriteItemId($itemId){
		$users = $this->favoriteDao->getUsersByFavoriteItemId($itemId);
		return $users;
	}
	
	function getFavoriteItem($itemId, $userId = null){
		if(!$userId) $userId = $this->getUserId();
		
		try{
			$favoriteItem = $this->favoriteDao->getByItemIdAndUserId($itemId, $userId);
		}catch(Exception $e){
			$favoriteItem = new SOYShop_FavoriteItem();
		}
		
		return $favoriteItem;
	}
}
?>