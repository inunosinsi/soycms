<?php
SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_DictionaryCategory");
abstract class SOYShop_AutoComplete_DictionaryCategoryDAO extends SOY2DAO {

	abstract function insert(SOYShop_AutoComplete_DictionaryCategory $bean);

	abstract function update(SOYShop_AutoComplete_DictionaryCategory $bean);

	/**
	 * @return object
	 */
	abstract function getByCategoryId($categoryId);

	abstract function deleteByCategoryId($categoryId);
}
