<?php
/**
 * @entity SOYShop_Site
 */
abstract class SOYShop_SiteDAO extends SOY2DAO{
	
	/**
	 * @trigger onInsert
	 * @return id
	 */
	abstract function insert(SOYShop_Site $obj);

   	/**
	 * @trigger onUpdate
	 * @return id
	 */	
	abstract function update(SOYShop_Site $obj);
	
	abstract function delete($id);
	
	abstract function get();

	/**
	 * @return object
	 */
	abstract function getBySiteId($siteId);
	
	/**
	 * @return object
	 */
	abstract function getById($id);

   	/**
   	 * @final
   	 */
   	function onInsert($query,$binds){
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
   		return array($query,$binds);
   	}

   	/**
   	 * @final
   	 */
   	function onUpdate($query,$binds){
		$binds[":updateDate"] = time();
		return array($query,$binds);
   	}
	
}
?>