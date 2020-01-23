<?php
SOY2::import("module.plugins.item_stock_manager.domain.SOYShop_StockHistory");
/**
 * @entity SOYShop_StockHistory
 */
abstract class SOYShop_StockHistoryDAO extends SOY2DAO{

	/**
	 * @index id
	 * @order id desc
	 */
    abstract function get();

   	/**
   	 * @return list
   	 * @order create_date desc
   	 */
   	abstract function getByItemId($itemId);

	/**
	 * @return id
	 * @trigger onInsert
	 */
   	abstract function insert(SOYShop_StockHistory $bean);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		return array($query, $binds);
	}
}
