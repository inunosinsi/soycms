<?php
/**
 * @entity site.SOYShop_Page
 */
abstract class SOYShop_PageDAO extends SOY2DAO{

    /**
     * @return id
     * @trigger onInsert
     */
    abstract function insert(SOYShop_Page $bean);

	/**
	 * @index id
	 */
    abstract function get();

    /**
     * @return object
     */
    abstract function getById($id);

    /**
     * @return object
     */
    abstract function getByUri($uri);

	/**
	 * @index id
	 */
    abstract function getByType($type);

    /**
     * @order #updateDate# desc
     */
    abstract function newPages();

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Page $bean);

	/**
	 * @columns id,config
	 */
	abstract function updateConfig(SOYShop_Page $bean);

	/**
	 * @tigger onDelete
	 */
	abstract function delete($id);

	/**
	 * @final
	 */
	function onInsert($query, $bind){
		$bind[":updateDate"] = $bind[":createDate"] = time();
		return array($query, $bind);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $bind){
		$bind[":updateDate"] = time();
		return array($query, $bind);
	}

	/**
	 * @final
	 */
	function onDelete($query, $bind){
		return array($query, $bind);
	}

	/**
	 * @column id,uri
	 * @query uri = :uri
	 */
	function checkUri($uri){

		$query = $this->getQuery();
		$result = $this->executeQuery($query, $this->getBinds());

		return (boolean)count($result);

	}
}
