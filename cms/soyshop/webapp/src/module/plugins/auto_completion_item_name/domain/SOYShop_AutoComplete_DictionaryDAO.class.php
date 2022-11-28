<?php
SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_Dictionary");
abstract class SOYShop_AutoComplete_DictionaryDAO extends SOY2DAO {

	abstract function insert(SOYShop_AutoComplete_Dictionary $bean);

	abstract function update(SOYShop_AutoComplete_Dictionary $bean);

	/**
	 * @return object
	 */
	abstract function getByItemId($itemId);

	abstract function deleteByItemId($itemId);
}
