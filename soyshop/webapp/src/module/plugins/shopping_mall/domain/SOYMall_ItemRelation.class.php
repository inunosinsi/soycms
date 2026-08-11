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
		return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
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
