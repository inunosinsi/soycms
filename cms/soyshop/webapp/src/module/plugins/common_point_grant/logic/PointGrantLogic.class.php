<?php

class PointGrantLogic extends SOY2LogicBase{

	const PONIT_PLUGIN_ID = "common_point_base";

	function __construct(){}

	function getPercentage(SOYShop_Item $item){
		static $p;
		//小商品の場合は親商品のポイント設定を調べる
		$itemId = (is_numeric($item->getType())) ? (int)$item->getType() : (int)$item->getId();

		$percentage = ($itemId > 0) ? soyshop_get_item_attribute_value($itemId, self::PONIT_PLUGIN_ID, "int") : null;
		if(!is_numeric($percentage)){
			if(is_null($p)){
				SOY2::import("module.plugins.common_point_grant.util.PointGrantUtil");
				$cnf = PointGrantUtil::getConfig();
				$p = (isset($cnf["percentage"])) ? (int)$cnf["percentage"] : 0;
			}
			$percentage = $p;
		}

		return self::getPercentageAfterCheckSale($itemId, $percentage);
	}

	function getPercentageAfterCheckSale(int $itemId, int $percentage){
		//商品IDしか渡せない箇所があるので、
		$item = soyshop_get_item_object($itemId);
		if(!is_numeric($item->getId())) return $percentage;

		//セール時のポイント設定
		if($item->getSaleFlag() != SOYShop_Item::IS_SALE) return $percentage;

		//セール期間であるかも見ておく
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("common_sale_period")){
			if(!SOY2Logic::createInstance("module.plugins.common_sale_period.logic.PriceLogic")->checkOnSale($item)) return $percentage;
		}

		SOY2::imports("module.plugins.common_point_grant.util.*");
		$cnf = PointGrantUtil::getConfig();

		if(isset($cnf["sale_point_double_on"]) && $cnf["sale_point_double_on"]){
			$percentage *= $cnf["sale_point_double"];
		}

		return $percentage;
	}
}
