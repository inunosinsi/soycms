<?php

class DiscountBulkBuyingConfigUtil {

	/* 割引内容 */
	const TYPE_AMOUNT = 1;//割引額
	const TYPE_PERCENT = 2;//割引率

	/* 公開状態 */
	const STATUS_INACTIVE = 0;//非公開、無効
	const STATUS_ACTIVE = 1;//公開、有効

	/**
	 * @return array() 割引設定各種
	 */
	public static function getDiscount(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		$discount = array();
		$discount["name"] = SOYShop_DataSets::get("discount_bulk_buying.name", "まとめ買い割引");
		$discount["description"] = SOYShop_DataSets::get("discount_bulk_buying.description", "");
		$discount["amount"] = SOYShop_DataSets::get("discount_bulk_buying.amount", 0);
		$discount["percent"] = SOYShop_DataSets::get("discount_bulk_buying.percent", null);
		$discount["type"] = SOYShop_DataSets::get("discount_bulk_buying.type", DiscountBulkBuyingConfigUtil::TYPE_AMOUNT);
		$discount["status"] = SOYShop_DataSets::get("discount_bulk_buying.status", DiscountBulkBuyingConfigUtil::STATUS_INACTIVE);

		return $discount;
	}

	/**
	 * @param array $discount
	 */
	public static function setDiscount($discount){
		SOYShop_DataSets::put("discount_bulk_buying.name", $discount["name"]);
		SOYShop_DataSets::put("discount_bulk_buying.description", $discount["description"]);
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOYShop_DataSets::put("discount_bulk_buying.amount", $discount["amount"]);
		SOYShop_DataSets::put("discount_bulk_buying.percent", $discount["percent"]);
		SOYShop_DataSets::put("discount_bulk_buying.type", $discount["type"]);
		SOYShop_DataSets::put("discount_bulk_buying.status", $discount["status"]);
	}

	/**
	 * 割引する金額の取得
	 * @param integer $price
	 * @return integer 割引額
	 */
	public static function getDiscountPrice($price){

		//合計金額が0以下の場合は、割引も0
		if($price <= 0){
			return 0;
		}


		$config = DiscountBulkBuyingConfigUtil::getDiscount();
		$discount = 0;
		//割引額
		if($config["type"] == DiscountBulkBuyingConfigUtil::TYPE_AMOUNT){

			//割引後に0円未満は除外
			if(is_numeric($price) && is_numeric($config["amount"]) && ($price - $config["amount"]) >= 0){
				$discount = $config["amount"];
			}


		//割引率
		}else if($config["type"] == DiscountBulkBuyingConfigUtil::TYPE_PERCENT){
			$percent = $config["percent"];

			//割引率は0以上
			if(is_numeric($percent) && $percent > 0){
				//小数点以下は切り捨て
				$discount = floor(($price / 100) * $percent);
			}

		}

		return $discount;
	}
}
