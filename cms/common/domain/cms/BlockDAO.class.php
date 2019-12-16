<?php

/**
 * @entity cms.Block
 */
abstract class BlockDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(Block $bean);

	/**
	 * @no_persistent #pageId#
	 */
	abstract function update(Block $bean);

	/**
	 * @columns id,object
	 */
	abstract function updateObject(Block $bean);

	abstract function delete($id);

	abstract function deleteByPageId($pageId);

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function get();

	abstract function getByPageId($pageId);

	/**
	 * @query page_id = :pageId and soy_id = :soyId
	 * @return object
	 */
	abstract function getPageBlock($pageId,$soyId);
}
