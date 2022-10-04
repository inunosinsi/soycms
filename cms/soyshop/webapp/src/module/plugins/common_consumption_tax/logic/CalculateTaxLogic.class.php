<?php

class CalculateTaxLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
	}

	/**
	 * @param CartLogic
	 * @return int total(通常商品の合計), int reducedRateTotal(軽減税率対象商品の合計)
	 */
	function getItemTotalByCart(CartLogic $cart, bool $isMod=true){
		$items = $cart->getItems();
		if(!count($items)) return array(0, 0);

		$total = 0;			//軽減税率対象商品を除いた商品の合算
		$reducedRateTotal = 0;	//軽減税率対象商品の合算
		foreach($items as $item){
			if(self::_isReducedTaxRateItem($item->getItemId())){
				$reducedRateTotal += $item->getTotalPrice();
			}else{
				$total += $item->getTotalPrice();
			}
		}

		if($total === 0 || !$isMod) return array($total, $reducedRateTotal);

		foreach($cart->getModules() as $mod){
			//値引き分も加味するので、isIncludeされていない値は0以上でなくても加算対象
			if(!$mod->getIsInclude()){
				$total += (int)$mod->getPrice();
			}
		}

		return array($total, $reducedRateTotal);
	}

	/**
	 * @param int, int
	 * @return int
	 */
	function calculateTaxTotal(int $total, int $reducedRateTotal=0){
		$taxRate = self::_getTaxRate();
		if($taxRate === 0) return 0;

		//軽減税率
		$reducedTaxRate = ($reducedRateTotal > 0) ? self::_getReducedTaxRate() : 0;

		$tax = self::_calculateTax($total, $taxRate);
		if($reducedTaxRate > 0) $tax += self::_calculateTax($reducedRateTotal, $reducedTaxRate);

		return $tax;
	}

	function calculateTax(int $price, int $rate){
		return self::_calculateTax($price, $rate);
	}

	private function _calculateTax($price, $rate){
		if($price == 0 || $rate == 0) return 0;
		$cnf = ConsumptionTaxUtil::getConfig();
		$m = (isset($cnf["method"])) ? $cnf["method"] : 0;

		switch($m){
			case ConsumptionTaxUtil::METHOD_ROUND:
				return (int)round($price * $rate / 100);
			case ConsumptionTaxUtil::METHOD_CEIL:
				return (int)ceil($price * $rate / 100);
			case ConsumptionTaxUtil::METHOD_FLOOR:
			default:
				return (int)floor($price * $rate / 100);
		}
	}

	function getTaxRate(){
		return self::_getTaxRate();
	}

	//軽減税率も加味した税率を取得
	function getTaxRateByItemId(int $itemId){
		$cnf = ConsumptionTaxUtil::getConfig();
		if(isset($cnf["reduced_tax_rate"]) && (int)$cnf["reduced_tax_rate"] > 0 && self::_isReducedTaxRateItem($itemId)){
			return (int)$cnf["reduced_tax_rate"];
		}else{
			return self::_getTaxRate();
		}
	}

	private function _getTaxRate(){
		static $rate;
		if(is_numeric($rate)) return $rate;
		$rate = 0;

		SOY2::imports("module.plugins.common_consumption_tax.domain.*");
		$scheduleDao = SOY2DAOFactory::create("SOYShop_ConsumptionTaxScheduleDAO");
		$scheduleDao->setLimit(1);

		try{
			$schedules =$scheduleDao->getScheduleByDate(time());
			if((isset($schedules[0]))) $rate = (int)$schedules[0]->getTaxRate();
		}catch(Exception $e){
			//
		}

		return $rate;
	}

	function getReducedTaxRate(){
		return self::_getReducedTaxRate();
	}

	private function _getReducedTaxRate(){
		$cnf = ConsumptionTaxUtil::getConfig();
		return (isset($cnf["reduced_tax_rate"])) ? (int)$cnf["reduced_tax_rate"] : 0;
	}

	function isReducedTaxRateItem(int $itemId){
		return self::_isReducedTaxRateItem($itemId);
	}

	//該当する商品が軽減率対象商品であるか？を判定する
	private function _isReducedTaxRateItem(int $itemId){
		if(!is_numeric($itemId)) return false;
		$list = self::_getList();
		if(!count($list)) return false;
		return (is_numeric(array_search($itemId, $list)));
	}

	function getItemIdsOfReducedTaxRate(){
		return self::_getList();
	}

	private static function _getList(){
		static $list;
		if(is_null($list)){
			$list = array();
			$sql = "SELECT item_id, item_value FROM soyshop_item_attribute WHERE item_field_id = :fieldId";
			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery($sql, array(":fieldId" => ConsumptionTaxUtil::FIELD_REDUCED_TAX_RATE));
			}catch(Exception $e){
				$res = array();
			}
			
			if(count($res)){
				foreach($res as $v){
					if(!is_string($v["item_value"]) || !strlen($v["item_value"])) continue;
					if(isset($v["item_id"]) && is_numeric($v["item_id"])) $list[] = (int)$v["item_id"];
				}
			}
		}
		return $list;
	}
}
