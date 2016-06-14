<?php
/**
 * @entity SOYShop_Campaign
 */
abstract class SOYShop_CampaignDAO extends SOY2DAO {
	
	/**
	 * @return id
	 */
	abstract function insert(SOYShop_Campaign $bean);
	
	/**
	 * @return list
	 * @query is_disabled != 1
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
}
?>