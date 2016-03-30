<?php
SOY2::imports("module.plugins.common_consumption_tax.domain.*");
class CommonConsumptionTaxCalculation extends SOYShopTaxCalculationBase{

	function calculation(CartLogic $cart){
		
		$items = $cart->getItems();
		if(count($items) === 0) return;
		
		$scheduleDao = SOY2DAOFactory::create("SOYShop_ConsumptionTaxScheduleDAO");
		$scheduleDao->setLimit(1);
		
		try{
			$schedules =$scheduleDao->getScheduleByDate(time());
		}catch(Exception $e){
			return;
		}
		
		if(!isset($schedules[0])) return;

		$taxRate = (int)$schedules[0]->getTaxRate();
			
		if($taxRate === 0) return;
		
		$totalPrice = 0;
		foreach($items as $item){
			$totalPrice += $item->getTotalPrice();
		}
		
		if($totalPrice === 0) return;
				
		//消費税がある場合
		SOY2::import("domain.order.SOYShop_ItemModule");
    	$module = new SOYShop_ItemModule();
		$module->setId("consumption_tax");
		$module->setName("消費税");
		$module->setType(SOYShop_ItemModule::TYPE_TAX);	//typeを指定しておくといいことがある
		$module->setPrice(floor($totalPrice * $taxRate / 100));
		$cart->addModule($module);
	}
}
SOYShopPlugin::extension("soyshop.tax.calculation", "common_consumption_tax", "CommonConsumptionTaxCalculation");
?>