<?php

class PriceLogic extends SOY2LogicBase{
	
	const PLUGIN_ID = "common_sale_period";
	const PERIOD_START = 0;
	const PERIOD_END = 2147483647;
	
	private $saleDao;
	private $onSale;
	
	function PriceLogic(){
		SOY2::imports("module.plugins." . self::PLUGIN_ID . ".domain.*");
		$this->saleDao = SOY2DAOFactory::create("SOYShop_SalePeriodDAO");
	}
	
	/** 管理画面周りのメソッド **/
	
	function save($itemId){
		try{
			$this->saleDao->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}
		
		$start = soyshop_convert_timestamp($_POST[self::PLUGIN_ID . "_start"], "start");
		$end = soyshop_convert_timestamp($_POST[self::PLUGIN_ID . "_end"], "end");
		
		$obj = new SOYShop_SalePeriod();
		$obj->setItemId($itemId);
		$obj->setSalePeriodStart($start);
		$obj->setSalePeriodEnd($end);
		
		try{
			$this->saleDao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
	
	/** 公開側周りのメソッド **/
	function getDisplayPrice(SOYShop_Item $item){
		return ($this->checkOnSale($item)) ? (int)$item->getSalePrice() : (int)$item->getPrice();
	}
	
	function getSaleDate($itemId, $mode = "start"){
		try{
			$obj = $this->saleDao->getByItemId($itemId);
		}catch(Exception $e){
			$obj = new SOYShop_SalePeriod();
		}
		
		return ($mode === "start") ? $obj->getSalePeriodStart() : $obj->getSalePeriodEnd();
	}
	
	//セール期間であるか調べる
	function checkOnSale(SOYShop_Item $item){
		if(!$item->getSaleFlag()) return false;
		return $this->saleDao->checkOnSale($item->getId());
	}
	
	/**　共通 **/
	function get($itemId){
		try{
			return $this->saleDao->getByItemId($itemId);
		}catch(Exception $e){
			return new SOYShop_SalePeriod();
		}
	}
}
?>