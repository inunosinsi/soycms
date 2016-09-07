<?php
SOY2::imports("module.plugins.common_consumption_tax.domain.*");
class CommonConsumptionTaxCalculation extends SOYShopTaxCalculationBase{

	function calculation(CartLogic $cart){
		$cart->removeModule("consumption_tax");
		
		$items = $cart->getItems();
		if(count($items) === 0) return;
		
		$totalPrice = 0;
		foreach($items as $item){
			$totalPrice += $item->getTotalPrice();
		}
		
		if($totalPrice === 0) return;
		
		foreach($cart->getModules() as $mod){
			//値引き分も加味するので、isIncludeされていない値は0以上でなくても加算対象
			if(!$mod->getIsInclude()){
				$totalPrice += (int)$mod->getPrice();
			}
		}
		
		$module = self::calcTax($totalPrice);
		
		if(!is_null($module)){
			$cart->addModule($module);
		}
	}
	
	//管理画面での外税計算
	function calculationOnEditPage($total){
		return self::calcTax($total);
	}
	
	//外税の計算
	private function calcTax($total){
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
		
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		$config = ConsumptionTaxUtil::getConfig();
		$m = (isset($config["method"])) ? $config["method"] : 0;
		switch($m){
			case ConsumptionTaxUtil::METHOD_ROUND:
				$price = round($total * $taxRate / 100);
				break;
			case ConsumptionTaxUtil::METHOD_CEIL:
				$price = ceil($total * $taxRate / 100);
				break;
			case ConsumptionTaxUtil::METHOD_FLOOR:
			default:
				$price = floor($total * $taxRate / 100);
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
?>