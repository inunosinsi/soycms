<?php

class DiscountBulkBuyingConditionUtil {

	/* 割引条件 */
	const COMBINATION_ALL = 1;//両方
	const COMBINATION_ANY = 2;//片方

	/**
	 * 割引条件の取得
	 * @return array
	 */
	public static function getCondition(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		$condition = array();
		$condition["price_checkbox"] = SOYShop_DataSets::get("discount_bulk_buying.condition.price_checkbox", 0);
		$condition["price_value"] = SOYShop_DataSets::get("discount_bulk_buying.condition.price_value", null);
		$condition["amount_checkbox"] = SOYShop_DataSets::get("discount_bulk_buying.condition.amount_checkbox", 0);
		$condition["amount_value"] = SOYShop_DataSets::get("discount_bulk_buying.condition.amount_value", null);
		$condition["combination"] = SOYShop_DataSets::get("discount_bulk_buying.condition.combination", DiscountBulkBuyingConditionUtil::COMBINATION_ALL);

		return $condition;
	}

	public static function setCondition($condition){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOYShop_DataSets::put("discount_bulk_buying.condition.price_checkbox", $condition["price_checkbox"]);
		SOYShop_DataSets::put("discount_bulk_buying.condition.price_value", $condition["price_value"]);
		SOYShop_DataSets::put("discount_bulk_buying.condition.amount_checkbox", $condition["amount_checkbox"]);
		SOYShop_DataSets::put("discount_bulk_buying.condition.amount_value", $condition["amount_value"]);
		SOYShop_DataSets::put("discount_bulk_buying.condition.combination", $condition["combination"]);
	}


	/**
	 * 割引条件を判断
	 * @param integer $price 合計金額
	 * @param integer $amount 商品の個数の合計
	 * @return boolean
	 */
	public static function hasDiscount($price=null, $amount=null){
		$condition =  DiscountBulkBuyingConditionUtil::getCondition();

		$checkList = array();

		//合計金額
		if($condition["price_checkbox"]){
			$checkList["price_check"] = DiscountBulkBuyingConditionUtil::checkPrice($condition, $price);
		}

		//合計商品個数
		if($condition["amount_checkbox"]){
			$checkList["amount_check"] = DiscountBulkBuyingConditionUtil::checkAmount($condition, $amount);
		}

		//一つも条件がない場合 無条件ボーナス
		if(count($checkList) == 0)return true;

		/* 条件の組み合わせ */
		$res = true;

		//両方
		if($condition["combination"] == DiscountBulkBuyingConditionUtil::COMBINATION_ALL){
			$res = !in_array(false, $checkList);

		//片方
		}else if($condition["combination"] == DiscountBulkBuyingConditionUtil::COMBINATION_ANY){
			$res = in_array(true, $checkList);
		}

		return $res;
	}

	/**
	 * 合計金額のチェック
	 * @param array $condition
	 * @param integer $price 注文合計金額
	 * @return boolean 条件適合していればtrue
	 */
	static function checkPrice($condition, $price){
		$res = true;

		//オフ 条件に含めない場合
		if(!$condition["price_checkbox"])return $res;

		//null, 空白対策
		if(empty($condition["price_calue"])){
			$condition["price_calue"] = 0;
		}

		if(empty($price)){
			$price = 0;
		}

		//合計金額 オン
		if($price < $condition["price_value"]){
			$res = false;
		}

		return $res;
	}

	/**
	 * 合計商品個数のチェック
	 * @param $condition
	 * @return boolean 条件適合していればtrue
	 */
	static function checkAmount($condition, $amount){
		$res = true;

		//オフ 条件に含めない場合
		if(!$condition["amount_checkbox"])return $res;

		//null, 空白対策
		if(empty($condition["amount_checkbox"])){
			$condition["amount_checkbox"] = 0;
		}

		if(empty($amount)){
			$amount = 0;
		}

		//合計商品個数 オン
		if($amount < $condition["amount_value"]){
			$res = false;
		}

		return $res;
	}

	/**
	 * cartから割引条件を判断
	 * @param CartLogic $order
	 * @return boolean trueなら条件に合致
	 */
	public static function hasDiscountByCart(CartLogic $cart){

		//合計金額
		$cartPrice = $cart->getItemPrice();

		//合計商品個数
		$amount = $cart->getOrderItemCount();

		return DiscountBulkBuyingConditionUtil::hasDiscount($cartPrice, $amount);
	}

	/**
	 * orderから割引条件を判断
	 * @param SOYShop_Order $order
	 * @return boolean trueなら条件に合致
	 */
	public static function hasDiscountByOrder(SOYShop_Order $order){

		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

		//合計金額
		$orderPrice = $logic->getTotalPrice($order->getId());//値引き前
		//$price = $order->getPrice(); 値引き後

		//合計商品個数
		$amount = $logic->getTotalOrderItemCountByItemId($order->getId());

		return DiscountBulkBuyingConditionUtil::hasDiscount($orderPrice, $amount);
	}
}
