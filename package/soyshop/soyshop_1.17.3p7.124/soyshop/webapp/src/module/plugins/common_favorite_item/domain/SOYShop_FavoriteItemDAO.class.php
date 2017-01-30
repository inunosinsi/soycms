<?php
/**
 * @entity SOYShop_FavoriteItem
 */
abstract class SOYShop_FavoriteItemDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_FavoriteItem $bean);
	
	/**
	 * trigger onUpdate
	 */
	abstract function update(SOYShop_FavoriteItem $bean);

	/**
	 * @return object
	 * @query item_id = :itemId AND user_id = :userId
	 */
	abstract function getByItemIdAndUserId($itemId, $userId);
	
	/**
	 * @query item_id = :itemId AND user_id = :userId
	 */
	abstract function deleteByItemIdAndUserId($itemId, $userId);
	
	function getFavoriteItems($userId){
		$sql = "SELECT i.* FROM soyshop_item i ".
				"JOIN soyshop_favorite_item f ".
				"ON i.id = f.item_id ".
				"WHERE f.user_id = :user_id ".
				"AND i.open_period_start <= " . time() . " ". 
				"AND i.open_period_end >= " . time() . " ". 
				"AND i.item_is_open = 1 ".
				"AND i.is_disabled = 0 ".
				"ORDER BY f.create_date DESC";
		$binds = array(":user_id" => $userId);
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
		
		if(count($results) === 0) return array();
		
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		$items = array();
		foreach($results as $result){
			$items[] = $itemDao->getObject($result);
		}
		
		return $items;
	}
	
	function getUsersByFavoriteItemId($itemId){
		$sql = "SELECT u.* FROM soyshop_user u ".
				"JOIN soyshop_favorite_item f ".
				"ON u.id = f.user_id ".
				"WHERE f.item_id = :item_id ".
				"AND u.is_disabled = 0 ".
				"ORDER BY f.create_date DESC";
		$binds = array(":item_id" => $itemId);
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
		
		if(count($results) === 0) return array();
		
		$users = array();
		
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		foreach($results as $result){
			if(!isset($result["id"])) continue;
			$users[] = $userDao->getObject($result);
		}
		
		return $users;
	}
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":purchased"] = 0;
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
}
?>