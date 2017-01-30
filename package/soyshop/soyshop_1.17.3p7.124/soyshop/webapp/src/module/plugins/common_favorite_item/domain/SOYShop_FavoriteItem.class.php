<?php
/**
 * @table soyshop_favorite_item
 */
class SOYShop_FavoriteItem {
	
	const PURCHASED = 1;
	const NOT_PURCHASED = 0;
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column item_id
	 */
	private $itemId;
	
	/**
	 * @column user_id
	 */
	private $userId;
	private $purchased;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
	
	function getPurchased(){
		return $this->purchased;
	}
	function setPurchased($purchased){
		$this->purchased = $purchased;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
?>