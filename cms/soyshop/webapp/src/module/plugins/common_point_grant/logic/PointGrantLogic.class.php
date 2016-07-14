<?php

class PointGrantLogic extends SOY2LogicBase{
	
	const PONIT_PLUGIN_ID = "common_point_base";
	
	private $itemAttributeDao;
	private $percentage;
	
	function PointGrantLogic(){
		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if(!$this->percentage){
			SOY2::imports("module.plugins.common_point_base.util.*");
			$config = PointBaseUtil::getConfig();
			$this->percentage = (int)$config["percentage"];
		}
	}
	
	function getPercentage(SOYShop_Item $item){
		try{
			$percentage = $this->itemAttributeDao->get($item->getId(), self::PONIT_PLUGIN_ID)->getValue();
		}catch(Exception $e){
			$percentage = $this->percentage;
		}
		
		return self::getPercentageAfterCheckSale($item->getId(), $percentage);
	}
	
	function getPercentageAfterCheckSale($itemId, $percentage){
		//商品IDしか渡せない箇所があるので、
		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
		}catch(Exception $e){
			return $percentage;
		}
		
		SOY2::imports("module.plugins.common_point_grant.util.*");
		$config = PointGrantUtil::getConfig();
		
		//セール時のポイント設定
		if($item->getSaleFlag() != SOYShop_Item::IS_SALE) return $percentage;
	
		//セール期間であるかも見ておく
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("common_sale_period")){
			if(!SOY2Logic::createInstance("module.plugins.common_sale_period.logic.PriceLogic")->checkOnSale($item)) return $percentage;
		}
			
		if(isset($config["sale_point_double_on"]) && $config["sale_point_double_on"]){
			$percentage *= $config["sale_point_double"];
		}
		
		return $percentage;		
	}
}
?>