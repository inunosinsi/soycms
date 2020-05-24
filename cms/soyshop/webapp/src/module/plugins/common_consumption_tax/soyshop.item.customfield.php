<?php

class CommonConsumptionTaxCustomField extends SOYShopItemCustomFieldBase{

	private $taxRate;

	function doPost(SOYShop_Item $item){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		if(isset($_POST["ReducedTaxRate"]) && $_POST["ReducedTaxRate"] == 1){
			$attr = self::getAttr($item->getId());
			$attr->setValue(1);
			try{
				self::dao()->insert($attr);
			}catch(Exception $e){
				//
			}
		}else{
			try{
				self::dao()->delete($item->getId(), ConsumptionTaxUtil::FIELD_REDUCED_TAX_RATE);
			}catch(Exception $e){
				//
			}
		}
	}

	function getForm(SOYShop_Item $item){
		$html = array();

		$taxRate = self::getTaxRate();
		if(isset($taxRate)){
			SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
			$config = ConsumptionTaxUtil::getConfig();
			if(isset($config["reduced_tax_rate"]) && (int)$config["reduced_tax_rate"] > 0){
				$html[] = "<div class=\"form-group\">";
				$html[] = "<label>軽減税率対象商品</label><br>";
				$html[] = "<label>";
				if(self::getAttr($item->getId())->getValue() == 1){
					$html[] = "<input type=\"checkbox\" name=\"ReducedTaxRate\" value=\"1\" checked=\"checked\">";
				}else{
					$html[] = "<input type=\"checkbox\" name=\"ReducedTaxRate\" value=\"1\">";
				}
				$html[] = $item->getName() . "を軽減税率の対象商品として扱う";
				$html[] = "</label>";
			}
		}

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		$taxRate = self::getTaxRate();

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

			//軽減税率を適用すべき商品か？
			if(isset($config["reduced_tax_rate"]) && (int)$config["reduced_tax_rate"] > 0 && ConsumptionTaxUtil::isReducedTaxRateItem($item->getId())){
				$taxRate = (int)$config["reduced_tax_rate"];
			}

			for($i = 0; $i < count($prices); $i++){
				$prices[$i] = (isset($prices[$i]) && is_numeric($prices[$i])) ? (int)$prices[$i] : 0;
				if($prices[$i] > 0){
					switch($m){
						case ConsumptionTaxUtil::METHOD_ROUND:
							$postTaxPrice = (int)round($prices[$i] * $taxRate / 100);
							break;
						case ConsumptionTaxUtil::METHOD_CEIL:
							$postTaxPrice = (int)ceil($prices[$i] * $taxRate / 100);
							break;
						case ConsumptionTaxUtil::METHOD_FLOOR:
						default:
							$postTaxPrice = (int)floor($prices[$i] * $taxRate / 100);
					}
					$prices[$i] += $postTaxPrice;
				}
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

	private function getTaxRate(){
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

	private function getAttr($itemId){
		try{
			return self::dao()->get($itemId, ConsumptionTaxUtil::FIELD_REDUCED_TAX_RATE);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(ConsumptionTaxUtil::FIELD_REDUCED_TAX_RATE);
			return $attr;
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_consumption_tax", "CommonConsumptionTaxCustomField");
