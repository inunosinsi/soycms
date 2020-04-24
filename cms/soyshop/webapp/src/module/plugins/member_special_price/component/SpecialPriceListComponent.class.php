<?php

class SpecialPriceListComponent extends HTMLList{
	
	private $itemId;
	private $priceLogic;
	
	protected function populateItem($entity, $idx) {
		
		$this->addLabel("label", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));
		
		$hash = (isset($entity["hash"])) ? $entity["hash"] : "none";
		$price = $this->priceLogic->getPriceByItemIdAndHash($this->itemId, $hash);
		$this->addInput("price", array(
			"name" => "member_special_price[" . $hash . "]",
			"value" => (isset($price)) ? $price : 0
		));
		
		$salePrice = $this->priceLogic->getPriceByItemIdAndHash($this->itemId, $hash, true);
		$this->addInput("sale_price", array(
			"name" => "member_special_price[" . $hash . "_sale]",
			"value" => (isset($salePrice)) ? $salePrice : 0
		));
	}
	
	
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	
	function setPriceLogic($priceLogic){
		$this->priceLogic = $priceLogic;
	}
}
?>