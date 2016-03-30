<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class SalePeriodCSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "common_sale_period";
	private $saleDao;

	function getLabel(){
		return "セール期間(開始日,終了日)";
	}

	/**
	 * export
	 * @param integer item_id
	 * @return value
	 */
	function export($itemId){
		$obj = self::getPriceLogic()->get($itemId);
		return soyshop_convert_date_string($obj->getSalePeriodStart()) . "," . soyshop_convert_date_string($obj->getSalePeriodEnd());
	}

	/**
	 * import
	 * void
	 */
	function import($itemId, $value){
		self::prepare();
		
		if(!strpos($value, ",") || strlen($value) === 0) $value . ",";
		$array = explode(",", $value);
		
		try{
			$this->saleDao->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}
		
		$start = soyshop_convert_timestamp($array[0], "start");
		$end = soyshop_convert_timestamp($array[1], "end");
		
		$obj = new SOYShop_SalePeriod();
		$obj->setItemId($itemId);
		$obj->setSalePeriodStart($start);
		$obj->setSalePeriodEnd($end);
		
		try{
			$this->saleDao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
	
	private $logic;
	
	private function getPriceLogic(){
		if(!$this->logic) $this->logic = SOY2Logic::createInstance("module.plugins.". self::PLUGIN_ID . ".logic.PriceLogic");
		return $this->logic;
	}
	
	private function prepare(){
		if(!$this->saleDao) {
			SOY2::imports("module.plugins." . self::PLUGIN_ID . ".domain.*");
			$this->saleDao = SOY2DAOFactory::create("SOYShop_SalePeriodDAO");
		}
	}
}

SOYShopPlugin::extension("soyshop.item.csv", "common_sale_period", "SalePeriodCSV");