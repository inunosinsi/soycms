<?php

class PriceLogic extends SOY2LogicBase{
	
	const PLUGIN_ID = "common_sale_period";
	const PERIOD_START = 0;
	const PERIOD_END = 2147483647;
	
	private $saleDao;
	private $onSale;
	
	function __construct(){
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
	
	/**
	 * @param int, string
	 * @return int
	 */
	function getSaleDate(int $itemId, string $mode="start"){
		static $_arr;
		if(!is_array($_arr)) $_arr = array();
		if(!isset($_arr[$itemId])) $_arr[$itemId] = array();

		if(isset($_arr[$itemId][$mode])) return $_arr[$itemId][$mode];

		try{
			$obj = $this->saleDao->getByItemId($itemId);
		}catch(Exception $e){
			$obj = new SOYShop_SalePeriod();
		}

		$v = "";
		switch($mode){
			case "start":
				$v = $obj->getSalePeriodStart();
				break;
			case "end":
			default:
				$v = $obj->getSalePeriodEnd();
				break;
		}

		$_arr[$itemId][$mode] = $v;
		return $_arr[$itemId][$mode];
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