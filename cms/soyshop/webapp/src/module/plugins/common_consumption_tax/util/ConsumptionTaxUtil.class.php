<?php

class ConsumptionTaxUtil{

	const METHOD_FLOOR = 0;	//端数を切り捨て
	const METHOD_ROUND = 1;	//端数を四捨五入
	const METHOD_CEIL = 2;	//端数を切り上げ

	const FIELD_REDUCED_TAX_RATE = "reduced_tax_rate";

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	public static function getConfig(){
		return self::_getConfig();
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("consumption_tax.config", array(
			"method" => 0,
			"reduced_tax_rate" => 0,
			"reduced_tax_rate_start_date" => "",
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("consumption_tax.config", $values);
	}

	public static function getTaxRate(){
		SOY2::imports("module.plugins.common_consumption_tax.domain.*");
		$scheduleDao = SOY2DAOFactory::create("SOYShop_ConsumptionTaxScheduleDAO");
		$scheduleDao->setLimit(1);

		try{
			$schedules =$scheduleDao->getScheduleByDate(time());
		}catch(Exception $e){
			return 0;
		}

		return (isset($schedules[0])) ? (int)$schedules[0]->getTaxRate() : 0;
	}

	public static function getReducedTaxRate(){
		$cnf = self::_getConfig();
		return (isset($cnf["reduced_tax_rate"])) ? (int)$cnf["reduced_tax_rate"] : 0;
	}

	public static function calculateTax($price, $rate){
		if($price == 0 || $rate == 0) return 0;
		$cnf = self::_getConfig();
		$m = (isset($cnf["method"])) ? $cnf["method"] : 0;

		switch($m){
			case self::METHOD_ROUND:
				return (int)round($price * $rate / 100);
			case self::METHOD_CEIL:
				return (int)ceil($price * $rate / 100);
			case self::METHOD_FLOOR:
			default:
				return (int)floor($price * $rate / 100);
		}
	}

	//該当する商品が軽減率対象商品であるか？を判定する
	public static function isReducedTaxRateItem($itemId){
		if(!is_numeric($itemId)) return false;
		$list = self::_getList();
		if(!count($list)) return false;
		return (is_numeric(array_search($itemId, $list)));
	}

	public static function getItemIdsOfReducedTaxRate(){
		return self::_getList();
	}

	private static function _getList(){
		static $list;
		if(is_null($list)){
			$list = array();
			$sql = "SELECT item_id FROM soyshop_item_attribute WHERE item_field_id = :fieldId";
			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery($sql, array(":fieldId" => self::FIELD_REDUCED_TAX_RATE));
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				foreach($res as $v){
					if(isset($v["item_id"]) && is_numeric($v["item_id"])) $list[] = (int)$v["item_id"];
				}
			}
		}
		return $list;
	}
}
