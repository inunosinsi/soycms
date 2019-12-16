<?php
/**
 * @table soyshop_sale_period
 */
class SOYShop_SalePeriod{
	
	/**
	 * @column item_id
	 */
	private $itemId;
	
	/**
	 * @column sale_period_start
	 */
	private $salePeriodStart;
	
	/**
	 * @column sale_period_end
	 */
	private $salePeriodEnd;
	
	function getItemId(){
		return (int)$this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	
	function getSalePeriodStart(){
		return (int)$this->salePeriodStart;
	}
	function setSalePeriodStart($salePeriodStart){
		$this->salePeriodStart = $salePeriodStart;
	}
	
	function getSalePeriodEnd(){
		return (int)$this->salePeriodEnd;
	}
	function setSalePeriodEnd($salePeriodEnd){
		$this->salePeriodEnd = $salePeriodEnd;
	}
}
?>