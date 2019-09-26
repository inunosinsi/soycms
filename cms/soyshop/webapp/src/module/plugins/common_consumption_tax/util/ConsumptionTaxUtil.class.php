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
		return SOYShop_DataSets::get("consumption_tax.config", array(
			"method" => 0,
			"reduced_tax_rate" => 0,
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("consumption_tax.config", $values);
	}

	//該当する商品が軽減率対象商品であるか？を判定する
	public static function isReducedTaxRateItem($itemId){
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

		if(!count($list)) return false;
		return (is_numeric(array_search($itemId, $list)));
	}
}
