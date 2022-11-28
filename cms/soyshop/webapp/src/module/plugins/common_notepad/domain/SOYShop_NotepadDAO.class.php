<?php
SOY2::import("module.plugins.common_notepad.domain.SOYShop_Notepad");
/**
 * @entity SOYShop_Notepad
 */
abstract class SOYShop_NotepadDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_Notepad $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Notepad $bean);

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @order create_date desc
	 */
	abstract function getByItemId($itemId);

	/**
	 * @order create_date desc
	 */
	abstract function getByCategoryId($categoryId);

	/**
	 * @order create_date desc
	 */
	abstract function getByUserId($userId);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(is_null($binds[":createDate"])) $binds[":createDate"] = time();
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
