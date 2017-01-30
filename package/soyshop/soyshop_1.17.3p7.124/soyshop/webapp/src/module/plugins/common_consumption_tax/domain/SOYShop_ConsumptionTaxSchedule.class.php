<?php
/**
 * @table soyshop_consumption_tax_schedule
 */
class SOYShop_ConsumptionTaxSchedule {

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column start_date
	 */
	private $startDate;
	
	/**
	 * @column tax_rate
	 */
	private $taxRate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getStartDate(){
		return $this->startDate;
	}
	function setStartDate($startDate){
		$this->startDate = $startDate;
	}
	
	function getTaxRate(){
		return $this->taxRate;
	}
	function setTaxRate($taxRate){
		$this->taxRate = $taxRate;
	}
}
?>