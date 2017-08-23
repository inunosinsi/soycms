<?php
/**
 * @entity admin.Site
 * @date 2007-08-22 18:42:19
 */
abstract class SiteDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(Site $bean);

	/**
	 * @no_persistent #siteId#
	 */
	abstract function update(Site $bean);

	abstract function delete($id);

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
	abstract function getBySiteId($siteId);

	/**
	 * @index id
	 * @column id,#siteId#
	 */
	abstract function getNameMap();

	/**
	 * @columns isDomainRoot
	 */
	abstract function resetDomainRootSite($isDomainRoot = 0);

	/**
	 * @columns isDomainRoot
	 * @query id = :id
	 */
	abstract function updateDomainRootSite($id,$isDomainRoot = 1);

	/**
	 * @query isDomainRoot = 1
	 * @return object
	 */
	abstract function getDomainRootSite();

	/**
	 * @index id
	 */
	abstract function getBySiteType($siteType);
}
