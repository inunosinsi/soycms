<?php

class SearchLogic extends SOY2LogicBase{
	
	const PLUGIN_ID = "common_sale_period";
	
	private $saleDao;
	
	function SearchLogic(){
		SOY2::imports("module.plugins." . self::PLUGIN_ID . ".domain.*");
		$this->saleDao = SOY2DAOFactory::create("SOYShop_SalePeriodDAO");
	}
	
	function searchItems($offset = 0, $limit = 10){
		return $this->saleDao->getSaleItems($offset, $limit);
	}
	
	function countItems(){
		return $this->saleDao->countSaleItems();
	}
}
?>