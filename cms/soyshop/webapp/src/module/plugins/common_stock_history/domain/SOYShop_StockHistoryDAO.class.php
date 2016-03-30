<?php
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
	 */
   	abstract function insert(SOYShop_StockHistory $bean);   	
}
?>