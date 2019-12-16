<?php
class CommonConsumptionTaxCalculation extends SOYShopTaxCalculationBase{

	function __construct(){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
	}

	function calculation(CartLogic $cart){
		$cart->removeModule("consumption_tax");
		$cart->clearOrderAttribute("reduced_tax_rate_mode");

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
		if($reducedRateTotal > 0){	//軽減税率対象期間内であることを登録しておく
			$cart->setOrderAttribute("reduced_tax_rate_mode", "軽減税率", "active", true, true);
		}

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
		$taxRate = ConsumptionTaxUtil::getTaxRate();
		if($taxRate === 0) return null;

		//軽減税率
		$reducedTaxRate = ($reducedRateTotal > 0) ? ConsumptionTaxUtil::getReducedTaxRate() : 0;

		$taxPrice = ConsumptionTaxUtil::calculateTax($total, $taxRate);
		if($reducedTaxRate > 0) $taxPrice += ConsumptionTaxUtil::calculateTax($reducedRateTotal, $reducedTaxRate);

		//消費税がある場合
		SOY2::import("domain.order.SOYShop_ItemModule");
    	$module = new SOYShop_ItemModule();
		$module->setId("consumption_tax");
		$module->setName("消費税");
		$module->setType(SOYShop_ItemModule::TYPE_TAX);	//typeを指定しておくといいことがある
		$module->setPrice($taxPrice);
		return $module;
	}
}
SOYShopPlugin::extension("soyshop.tax.calculation", "common_consumption_tax", "CommonConsumptionTaxCalculation");
