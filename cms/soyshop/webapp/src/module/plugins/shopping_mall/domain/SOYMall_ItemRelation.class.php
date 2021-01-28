<?php
/**
 * @table soymall_item_relation
 */
class SOYMall_ItemRelation {

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column admin_id
	 */
	private $adminId;

	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getAdminId(){
		return $this->adminId;
	}
	function setAdminId($adminId){
		$this->adminId = $adminId;
	}
}
