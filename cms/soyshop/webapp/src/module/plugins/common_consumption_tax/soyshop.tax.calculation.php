<?php
SOY2::imports("module.plugins.common_consumption_tax.domain.*");
class CommonConsumptionTaxCalculation extends SOYShopTaxCalculationBase{

	function __construct(){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
	}

	function calculation(CartLogic $cart){
		$cart->removeModule("consumption_tax");

		$items = $cart->getItems();
		if(count($items) === 0) return;

		$total = 0;			//軽減税率対象商品を除いた商品の合算
		$reducedRateTotal = 0;	//軽減税率対象商品の合算
		foreach($items as $item){
			if(ConsumptionTaxUtil::isReducedTaxRateItem($item->getItemId())){
				$reducedRateTotal += $item->getTotalPrice();
			}else{
				$total += $item->getTotalPrice();
			}
		}

		if($total === 0 && $reducedRateTotal === 0) return;

		foreach($cart->getModules() as $mod){
			//値引き分も加味するので、isIncludeされていない値は0以上でなくても加算対象
			if(!$mod->getIsInclude()){
				$total += (int)$mod->getPrice();
			}
		}

		$module = self::calcTax($total, $reducedRateTotal);

		if(!is_null($module)){
			$cart->addModule($module);
		}
	}

	//管理画面での外税計算
	function calculationOnEditPage($total, $reducedRateTotal){
		//@ToDo 注文情報を再取得して再計算する仕組みを設ける
		return self::calcTax($total, $reducedRateTotal);
	}

	//外税の計算
	private function calcTax($total, $reducedRateTotal=0){
		$scheduleDao = SOY2DAOFactory::create("SOYShop_ConsumptionTaxScheduleDAO");
		$scheduleDao->setLimit(1);

		try{
			$schedules =$scheduleDao->getScheduleByDate(time());
		}catch(Exception $e){
			return null;
		}

		if(!isset($schedules[0])) return null;

		$taxRate = (int)$schedules[0]->getTaxRate();
		if($taxRate === 0) return null;

		$config = ConsumptionTaxUtil::getConfig();
		$reducedTaxRate = ($reducedRateTotal > 0 && isset($config["reduced_tax_rate"])) ? (int)$config["reduced_tax_rate"] : 0;	//軽減税率 @軽減税率の設定

		$m = (isset($config["method"])) ? $config["method"] : 0;
		switch($m){
			case ConsumptionTaxUtil::METHOD_ROUND:
				$price = (int)round($total * $taxRate / 100);
				if($reducedTaxRate > 0) $price += (int)round($reducedRateTotal * $reducedTaxRate / 100);
				break;
			case ConsumptionTaxUtil::METHOD_CEIL:
				$price = (int)ceil($total * $taxRate / 100);
				if($reducedTaxRate > 0) $price += (int)ceil($reducedRateTotal * $reducedTaxRate / 100);
				break;
			case ConsumptionTaxUtil::METHOD_FLOOR:
			default:
				$price = (int)floor($total * $taxRate / 100);
				if($reducedTaxRate > 0) $price += (int)floor($reducedRateTotal * $reducedTaxRate / 100);
		}

		//消費税がある場合
		SOY2::import("domain.order.SOYShop_ItemModule");
    	$module = new SOYShop_ItemModule();
		$module->setId("consumption_tax");
		$module->setName("消費税");
		$module->setType(SOYShop_ItemModule::TYPE_TAX);	//typeを指定しておくといいことがある
		$module->setPrice($price);

		return $module;
	}
}
SOYShopPlugin::extension("soyshop.tax.calculation", "common_consumption_tax", "CommonConsumptionTaxCalculation");
