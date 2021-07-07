<?php
class CommonConsumptionTaxCalculation extends SOYShopTaxCalculationBase{

	function __construct(){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
	}

	function calculation(CartLogic $cart){
		$cart->removeModule("consumption_tax");
		$cart->clearOrderAttribute("reduced_tax_rate_mode");

		$taxLogic = SOY2Logic::createInstance("module.plugins.common_consumption_tax.logic.CalculateTaxLogic");
		list($total, $reducedRateTotal) = $taxLogic->getItemTotalByCart($cart);

		//軽減税率対象期間内であることを登録しておく
		if($reducedRateTotal > 0){
			$cart->setOrderAttribute("reduced_tax_rate_mode", "軽減税率", "active", true, true);
		}

		$tax = $taxLogic->calculateTaxTotal($total, $reducedRateTotal);
		$mod = self::_setTaxModule($tax);

		if($mod instanceof SOYShop_ItemModule){
			$cart->addModule($mod);
		}
	}

	//管理画面での外税計算
	function calculationOnEditPage(int $total, int $reducedRateTotal){
		$tax = SOY2Logic::createInstance("module.plugins.common_consumption_tax.logic.CalculateTaxLogic")->calculateTaxTotal($total, $reducedRateTotal);
		return self::_setTaxModule($tax);
	}

	private function _setTaxModule(int $tax){
		SOY2::import("domain.order.SOYShop_ItemModule");
    	$mod = new SOYShop_ItemModule();
		$mod->setId("consumption_tax");
		$mod->setName("消費税");
		$mod->setType(SOYShop_ItemModule::TYPE_TAX);	//typeを指定しておくといいことがある
		$mod->setPrice($tax);
		return $mod;
	}
}
SOYShopPlugin::extension("soyshop.tax.calculation", "common_consumption_tax", "CommonConsumptionTaxCalculation");
