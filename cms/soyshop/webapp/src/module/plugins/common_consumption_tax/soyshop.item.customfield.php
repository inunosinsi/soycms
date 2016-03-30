<?php

class CommonConsumptionTaxCustomField extends SOYShopItemCustomFieldBase{

	private $taxRate;

	function doPost(SOYShop_Item $item){}

	function getForm(SOYShop_Item $item){}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$taxRate = $this->getTaxRate();
		$sellingPrice = $item->getSellingPrice();
		
		if(isset($taxRate)){
			$postTaxPrice = floor($sellingPrice * $taxRate / 100);
			$sellingPrice += $postTaxPrice;
		}
		
		$htmlObj->addLabel("post_tax_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($sellingPrice)
		));
		
	}

	function onDelete($id){}
	
	function getTaxRate(){
		if(is_null($this->taxRate)){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$config = SOYShop_ShopConfig::load();
			if($config->getConsumptionTax() == 1){
				SOY2::imports("module.plugins.common_consumption_tax.domain.*");
				$scheduleDao = SOY2DAOFactory::create("SOYShop_ConsumptionTaxScheduleDAO");
				$scheduleDao->setLimit(1);
				
				try{
					$schedules = $scheduleDao->getScheduleByDate(time());
				}catch(Exception $e){
					$schedules = array();
				}
				
				if(isset($schedules[0])){
					$this->taxRate = (int)$schedules[0]->getTaxRate();
					return $this->taxRate;
				}
			}
			
			$this->taxRate = 0;	
		}
		
		return $this->taxRate;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_consumption_tax", "CommonConsumptionTaxCustomField");
?>