<?php
/**
 * @entity SOYShop_Connector
 */
abstract class SOYShop_ConnectorDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
    abstract function insert(SOYShop_Connector $connector);
    
    abstract function update(SOYShop_Connector $connector);
    
    abstract function get();
    
    /**
     * @return object
     */
    abstract function getById($id);
    
    abstract function delete($id);
    
    /**
     * @return column_shop_db_path
     * @columns shop_db_path
     * @query id
     */
    abstract function getPathById($id);

	/**
	 * @return column_count_user
	 * @columns count(id) as count_user
	 */
	abstract function countUser();
}
?>