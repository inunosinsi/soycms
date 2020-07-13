<?php

class SimpleAggregateUtil{

	const MODE_MONTH 	= "month";
	const MODE_DAY 		= "day";
	const MODE_ITEMRATE = "itemrate";
	const MODE_AGE 		= "age";
	const MODE_CUSTOMER 	= "customer";
	const MODE_ORDER_DATE_CUSTOMFIELD 	= "order_date_customfield"; //隠しモード

	/**
	 * 隠しモードの使い方
	 * name属性でAggregateHiddenValue[label]、AggregateHiddenValue[date_field_id]とAggregateHiddenValue[first_column]を渡す
	 * オーダーカスタムフィールドの値も使用したい場合はAggregateHiddenValue[field_id]とAggregateHiddenValue[field_value]で使用可
	 */

	const TYPE_MONTH 		= 	"月次売上集計";
	const TYPE_DAY 			= 	"日次売上集計";
	const TYPE_ITEMRATE 	= 	"商品毎の売上集計";
	const TYPE_AGE			= 	"年齢別売上集計";
	const TYPE_CUSTOMER 	= "顧客ごと売上集計";

	const METHOD_MODE_TAX 			= "tax";
	const METHOD_MODE_COMMISSION 	= "commission";
	const METHOD_MODE_POINT 		= "point";
	const METHOD_MODE_DISCOUNT 		= "discount";

	const METHOD_INCLUDE_TAX 		= "消費税込み";
	const METHOD_INCLUDE_COMMISSION = "手数料込み";
	const METHOD_INCLUDE_POINT 		= "ポイント値引き込み";
	const METHOD_INCLUDE_DISCOUNT 	= "クーポン値引き等込み";

	/**
	 * タイトルを取得する
	 */
	public static function getTitle(){

		$mode = (isset($_POST["Aggregate"]["type"])) ? $_POST["Aggregate"]["type"] : "month";
		switch($mode){
			case self::MODE_ITEMRATE:
				return self::TYPE_ITEMRATE;
			case self::MODE_DAY:
				return self::TYPE_DAY;
			case self::MODE_AGE:
				return self::TYPE_AGE;
			case self::MODE_CUSTOMER:
				return self::TYPE_CUSTOMER;
			case self::MODE_MONTH:
			default:
				return self::TYPE_MONTH;
		}
	}

	//消費税等を除いた値を返す
	public static function priceFilter(SOYShop_Order $order){
		self::_filterCondition();

		$price = $order->getPrice();
		$mods = $order->getModuleList();
		if(!count($mods)) return $price;

		foreach($mods as $key => $mod){
			//合算に関するもののみ
			if(!$mod->getIsInclude()){
				//消費税や手数料等
				if($mod->getPrice() > 0){
					if(AGGREGATE_WITHOUT_TAX && strpos($mod->getType(), "tax") !== false){
						$price -= (int)$mod->getPrice();
					}else if(AGGREGATE_WITHOUT_COMMISSION && (strpos($mod->getType(), "delivery_") !== false || strpos($mod->getType(), "payment_") !== false)){
						$price -= (int)$mod->getPrice();
					}

				//ポイントやクーポン等
				}else if($mod->getPrice() < 0){
					//ポイント
					if(AGGREGATE_WITHOUT_POINT && strpos($mod->getType(), "point") !== false){
						$price -= (int)$mod->getPrice();
					} else {
						//ポイント以外すべて
						if(AGGREGATE_WITHOUT_DISCOUNT && strpos($mod->getType(), "point") === false){
							$price -= (int)$mod->getPrice();
						}
					}
				}
			}
		}

		return $price;
	}

	private static function _filterCondition(){
		if(!defined("AGGREGATE_WITHOUT_TAX")){
			//集計方法
			$methods = (isset($_POST["Aggregate"]["method"])) ? $_POST["Aggregate"]["method"] : array();

			//消費税を含めない
			define("AGGREGATE_WITHOUT_TAX", !in_array(self::METHOD_MODE_TAX, $methods));

			//手数料を含めない
			define("AGGREGATE_WITHOUT_COMMISSION", !in_array(self::METHOD_MODE_COMMISSION, $methods));

			//ポイント値引きを含めない
			define("AGGREGATE_WITHOUT_POINT", !in_array(self::METHOD_MODE_POINT, $methods));

			//クーポン等値引きを含めない
			define("AGGREGATE_WITHOUT_DISCOUNT", !in_array(self::METHOD_MODE_DISCOUNT, $methods));
		}
	}

	public static function getItemOrdersByOrderId($orderId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		try{
			return $dao->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}
}
