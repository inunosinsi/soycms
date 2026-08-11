<?php

class DiscountBulkBuyingUtil {

	/* 割引内容 */
	const TYPE_AMOUNT = 1;//割引額
	const TYPE_PERCENT = 2;//割引率

	/* 割引条件 */
	const COMBINATION_ALL = 1;//両方
	const COMBINATION_ANY = 2;//片方

	public static function getDiscountType(){
		return array(
			self::TYPE_AMOUNT => "割引額",
			self::TYPE_PERCENT => "割引率"
		);
	}

	public static function getCombinationType(){
		return array(
			self::COMBINATION_ALL => "両方",
			self::COMBINATION_ANY => "片方"
		);
	}

	public static function getConfig(){
		return SOYShop_DataSets::get("discount_bulk_buying_each_category.config", array(
			"name" => "まとめ買い割引",
			"description" => "",
			// "type" => self::TYPE_AMOUNT,
			// "amount" => 0,
			// "percent" => 0
		));
	}

	public static function saveConfig(array $values){
		SOYShop_DataSets::put("discount_bulk_buying_each_category.config", $values);
	}

	public static function getCondition(){
		return SOYShop_DataSets::get("discount_bulk_buying_each_category.condition", array(
			"price" => array("active" => 0, "value" => 0),
			"amount" => array("active" => 0, "value" => 0),
			"combination" => self::COMBINATION_ALL
		));
	}

	public static function saveCondition(array $values){
		foreach(array("price", "amount") as $t){
			foreach(array("active", "value") as $tt){
				if(!isset($values[$t][$tt])) $values[$t][$tt] = 0;
			}
		}

		SOYShop_DataSets::put("discount_bulk_buying_each_category.condition", $values);
	}

	public static function getCategoryCondition(){
		return SOYShop_DataSets::get("discount_bulk_buying_each_category.category", array());
	}

	public static function saveCategoryCondition($values){
		ksort($values);
		return SOYShop_DataSets::put("discount_bulk_buying_each_category.category", $values);
	}

	public static function getCategoryCombinationCondition(){
		return SOYShop_DataSets::get("discount_bulk_buying_each_category.cat_comb", self::COMBINATION_ALL);
	}

	public static function saveCategoryCombinationCondition(int $i){
		return SOYShop_DataSets::put("discount_bulk_buying_each_category.cat_comb", $i);
	}
}
