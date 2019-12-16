<?php

/**
 * @entity SOYShop_ListPriceLog
 */
abstract class SOYShop_ListPriceLogDAO extends SOY2DAO{

	abstract function insert(SOYShop_ListPriceLog $bean);

	/**
	 * @return object
	 */
	abstract function getByItemOrderId($itemOrderId);
}
