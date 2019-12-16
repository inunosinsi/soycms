<?php

/**
 * @table soyshop_list_price_log_when_order
 */
class SOYShop_ListPriceLog {

	/**
	 * @column item_order_id
	 */
	private $itemOrderId;

	/**
	 * @column list_price
	 */
	private $listPrice;

	function getItemOrderId(){
		return $this->itemOrderId;
	}
	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}

	function getListPrice(){
		return $this->listPrice;
	}
	function setListPrice($listPrice){
		$this->listPrice = $listPrice;
	}
}
