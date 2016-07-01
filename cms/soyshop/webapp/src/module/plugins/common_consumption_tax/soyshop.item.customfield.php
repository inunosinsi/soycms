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
		
		//0:表示価格 1:通常価格 2:セール価格
		$prices = array(
			$item->getSellingPrice(),
			$item->getPrice(),
			$item->getSalePrice()
		);
		
		if(isset($taxRate)){
			SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
			$config = ConsumptionTaxUtil::getConfig();
			$m = (isset($config["method"])) ? $config["method"] : 0;
			for($i = 0; $i < count($prices); $i++){
				switch($m){
					case ConsumptionTaxUtil::METHOD_ROUND:
						$postTaxPrice = round($prices[$i] * $taxRate / 100);
						break;
					case ConsumptionTaxUtil::METHOD_CEIL:
						$postTaxPrice = ceil($prices[$i] * $taxRate / 100);
						break;
					case ConsumptionTaxUtil::METHOD_FLOOR:
					default:
						$postTaxPrice = floor($prices[$i] * $taxRate / 100);
				}
				
				$prices[$i] += $postTaxPrice;
			}
				
		}
		
		$htmlObj->addLabel("post_tax_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($prices[0])
		));
		
		$htmlObj->addLabel("post_tax_normal_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($prices[1])
		));
		
		$htmlObj->addLabel("post_tax_sale_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($prices[2])
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