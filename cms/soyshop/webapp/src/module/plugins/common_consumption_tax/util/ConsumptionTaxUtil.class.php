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

	public static function saveReducedTaxRateItem(bool $on, int $itemId){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if($on){	//save
			try{
				$attr = $dao->get($itemId, self::FIELD_REDUCED_TAX_RATE);
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($itemId);
				$attr->setFieldId(self::FIELD_REDUCED_TAX_RATE);
			}

			$attr->setValue(1);

			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){

				}
			}
		}else{
			try{
				$dao->delete($itemId, self::FIELD_REDUCED_TAX_RATE);
			}catch(Exception $e){
				//
			}
		}
	}
}
