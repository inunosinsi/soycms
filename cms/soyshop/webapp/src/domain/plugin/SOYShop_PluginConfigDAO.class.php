<?php
/**
 * @entity plugin.SOYShop_PluginConfig
 */
abstract class SOYShop_PluginConfigDAO extends SOY2DAO{

	/**
     * @final
     * @return boolean
     * @see plugin.SOYShop_PluginConfigDAO#isActiveImpl
     */
    function isActive($id){
    	try{
    		return (boolean)$this->isActiveImpl($id);
    	}catch(Exception $e){
    		return false;
    	}
    }

    /**
     * @columns #isActive#
     * @return column_is_active
     * @query #id# = :id;
     */
    abstract function isActiveImpl($id);

	/**
	 * @order display_order ASC, plugin_id ASC, id ASC
	 */
    abstract function get();


    /**
     * @query #isActive# = 1
     * @order display_order ASC, plugin_id ASC, id ASC
     */
    abstract function getActiveModules();

	/**
	 * @return object
	 */
  	abstract function getById($id);

	/**
	 * @return object
	 */
	abstract function getByPluginId($pluginId);

	/**
	 * @order display_order ASC, plugin_id ASC, id ASC
	 */
	abstract function getByType($type);

	/**
	 * @query plugin_type = :type AND is_active = :isActive
	 */
	abstract function getByTypeAndIsActive($type, $isActive);

	/**
	 *
	 * @return id
	 */
  abstract function insert(SOYShop_PluginConfig $soyshopmodule);

	abstract function update(SOYShop_PluginConfig $soyshopmodule);

}
