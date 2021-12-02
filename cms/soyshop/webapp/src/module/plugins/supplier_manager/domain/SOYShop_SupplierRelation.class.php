<?php
/**
 * @table soyshop_supplier_relation
 */
class SOYShop_SupplierRelation {

	/**
	 * @column supplier_id
	 */
	private $supplierId;

	/**
	 * @column item_id
	 */
	private $itemId;

	function getSupplierId(){
		return $this->supplierId;
	}
	function setSupplierId($supplierId){
		$this->supplierId = $supplierId;
	}

	function getItemId(){
		return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
