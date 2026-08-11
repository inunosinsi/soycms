<?php
class DeliveryNormalUtil{

	//送料無料の例外設定の定数
	const PATTERN_AND = 0;	//カートに指定の商品がすべて含まれている時
	const PATTERN_OR = 1;	//カートに指定の商品のどれかが含まれている時
	const PATTERN_MATCH = 2;	//カートに指定の商品のみの時

	const SHORTEST_DAY_ALFER = 2;

	public static function getFreePrice(){
		return SOYShop_DataSets::get("delivery.default.free_price", array(
			"free" => null
		));
	}

	public static function saveFreePrice($values){
		$values["free"] = mb_convert_kana($values["free"], "a");
		$values["free"] = (is_numeric($values["free"])) ? $values["free"] : null;
		SOYShop_DataSets::put("delivery.default.free_price", $values);
	}

	public static function getPrice(){
		return SOYShop_DataSets::get("delivery.default.prices", array());
	}

	public static function savePrice($values){
		SOYShop_DataSets::put("delivery.default.prices", $values);
	}

	public static function getUseDeliveryTimeConfig(){
		return SOYShop_DataSets::get("delivery.default.use.time", array(
			"use" => 1
		));
	}

	public static function saveUseDeliveryTimeConfig($values){
		SOYShop_DataSets::put("delivery.default.use.time", $values);
	}

	public static function getDeliveryTimeConfig(){
		return SOYShop_DataSets::get("delivery.default.delivery_time_config", array(
			"希望なし", "午前中", "12時～14時", "14時～16時", "16時～18時", "18時～20時", "20時〜21時"
		));
	}

	public static function saveDeliveryTimeConfig($values){
		$config = array_diff($values, array(""));
		SOYShop_DataSets::put("delivery.default.delivery_time_config", $config);
	}

	public static function getDeliveryDateConfig(){
		return SOYShop_DataSets::get("delivery.default.delivery_date.config", array(
			"use_delivery_date" => 0,
			"use_delivery_date_unspecified" => 1,
			"delivery_shortest_date" => 2,
			"use_re_calc_shortest_date" => 1,
			"delivery_date_period" => 7,
			"delivery_date_format" => "Y年m月d日(#w#)",
			"delivery_date_mail_insert_date" => 0
		));
	}

	public static function saveDeliveryDateConfig($values){
		$values["use_delivery_date"] = (isset($values["use_delivery_date"])) ? (int)$values["use_delivery_date"] : 0;
		$values["use_format_calendar"] = (isset($values["use_format_calendar"])) ? (int)$values["use_format_calendar"] : 0;
		$values["use_delivery_date_unspecified"] = (isset($values["use_delivery_date_unspecified"])) ? (int)$values["use_delivery_date_unspecified"] : 0;
		SOYShop_DataSets::put("delivery.default.delivery_date.config", $values);
	}

	public static function getDeliveryDateAfterConfig($mode=1){
		return SOYShop_DataSets::get("delivery.default.delivery_date_after_".$mode.".config", array());
	}

	/**
	 * @param array, int
	 */
	public static function saveDeliveryDateAfterConfig($values, $mode=1){
		SOYShop_DataSets::put("delivery.default.delivery_date_after_".$mode.".config", $values);
	}

	public static function getExceptionFeeConfig(){
		return SOYShop_DataSets::get("delivery.default.fee_exception.config", array());
	}

	public static function saveExceptionFeeConfig(array $cnfs){
		$logic = SOY2Logic::createInstance("module.plugins.delivery_normal.logic.FeeExceptionLogic");
		$arr = array();	//各設定の商品コードが一つでもあれば格納しておく
		foreach($cnfs as $cnf){
			if(!is_array($cnf["code"])) continue;
			$cnf["code"] = $logic->checkIsExistItemCodes($cnf["code"]);
			if(!count($cnf["code"])) continue;
			$cnf["pattern"] = (isset($cnf["pattern"]) && is_numeric($cnf["pattern"])) ? (int)$cnf["pattern"] : self::PATTERN_OR;
			$arr[] = $cnf;
		}
		SOYShop_DataSets::put("delivery.default.fee_exception.config", $arr);
	}

	public static function getTitle(){
		return SOYShop_DataSets::get("delivery.default.title", "宅配便");
	}

	public static function saveTitle($value){
		SOYShop_DataSets::put("delivery.default.title", $value);
	}

	public static function getDescription(){
		return SOYShop_DataSets::get("delivery.default.description", "宅配便で配送します。");
	}

	public static function saveDescription($value){
		SOYShop_DataSets::put("delivery.default.description", $value);
	}

	/**
	 * @return int
	 */
	public static function getShortestDate(){
		$cnf = self::getDeliveryDateConfig();
		$d = (isset($cnf["delivery_shortest_date"])) ? (int)$cnf["delivery_shortest_date"] : 0;

		// 地域性を加味
		$area = (int)CartLogic::getCart()->getCustomerInformation()->getArea();
		if($area === 0) return $d;

		for($i = 1; $i <= self::SHORTEST_DAY_ALFER; $i++){
			$afterCnf = self::getDeliveryDateAfterConfig($i);
			if(!is_array($afterCnf) || !count($afterCnf) || is_bool(array_search((string)$area, $afterCnf))) continue;

			$d += 1;
		}

		return $d;
	}

	/**
	 * @param array
	 * @return array
	 */
	public static function getDeliveryDateOptions(array $config){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.delivery_normal.logic.DeliveryDateFormatLogic");

		//最短の日付を取得
		$time = time();

		//営業日を加味
		if(
			isset($config["use_re_calc_shortest_date"]) &&
			$config["use_re_calc_shortest_date"] == 1 &&
			SOYShopPluginUtil::checkIsActive("parts_calendar")
		){
			$time = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.BusinessDateLogic")->getNextBusinessDate();
		}

		$shortest = $time + self::getShortestDate() * 24 * 60 * 60;
		$last = $shortest + (int)$config["delivery_date_period"] * 24 * 60 * 60;

		$opts = array();

		//指定なしの項目を追加
		if(isset($config["use_delivery_date_unspecified"]) && $config["use_delivery_date_unspecified"] == 1){
			$opts[] = "指定なし";
		}

		do{
			$opts[date("Y-m-d", $shortest)] = $logic->convertDateString($config["delivery_date_format"], $shortest);
			$shortest += 24 * 60 * 60;
		}while($shortest < $last);

		return $opts;
	}

	public static function getPatternText(int $pat){
		switch($pat){
			case self::PATTERN_AND:
				return "全ての商品を含む場合は配送料無料";
			case self::PATTERN_OR:
				return "どれか一つの商品がある場合は配送料無料";
			case self::PATTERN_MATCH:
				return "カートに入っている商品が指定の商品のみの場合は配送料無料";
		}
	}
}
