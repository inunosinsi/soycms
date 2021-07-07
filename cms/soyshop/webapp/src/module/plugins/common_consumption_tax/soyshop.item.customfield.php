<?php

class CommonConsumptionTaxCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		$on = (isset($_POST["ReducedTaxRate"]) && $_POST["ReducedTaxRate"] == 1);
		ConsumptionTaxUtil::saveReducedTaxRateItem($on, $item->getId());
	}

	function getForm(SOYShop_Item $item){
		$taxLogic = SOY2Logic::createInstance("module.plugins.common_consumption_tax.logic.CalculateTaxLogic");

		$taxRate = $taxLogic->getTaxRate();
		if(!isset($taxRate)) return "";

		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		$cnf = ConsumptionTaxUtil::getConfig();
		if(!isset($cnf["reduced_tax_rate"]) || (int)$cnf["reduced_tax_rate"] === 0) return "";

		$html = array();

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>軽減税率対象商品</label><br>";
		$html[] = "<label>";
		if(is_numeric($item->getId()) && $taxLogic->isReducedTaxRateItem($item->getId())){
			$html[] = "<input type=\"checkbox\" name=\"ReducedTaxRate\" value=\"1\" checked=\"checked\">";
		}else{
			$html[] = "<input type=\"checkbox\" name=\"ReducedTaxRate\" value=\"1\">";
		}
		$html[] = $item->getName() . "を軽減税率の対象商品として扱う";
		$html[] = "</label>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//0:表示価格 1:通常価格 2:セール価格
		$prices = array(
			$item->getSellingPrice(),
			$item->getPrice(),
			$item->getSalePrice()
		);

		$taxLogic = SOY2Logic::createInstance("module.plugins.common_consumption_tax.logic.CalculateTaxLogic");

		//軽減税率を加味した税率を取得
		//SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		$taxRate = (is_numeric($item->getId())) ? $taxLogic->getTaxRateByItemId($item->getId()) : 0;

		if(isset($taxRate)){
			for($i = 0; $i < count($prices); $i++){
				$prices[$i] = (isset($prices[$i]) && is_numeric($prices[$i])) ? (int)$prices[$i] : 0;
				if($prices[$i] > 0) $prices[$i] += $taxLogic->calculateTax($prices[$i], $taxRate);
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
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_consumption_tax", "CommonConsumptionTaxCustomField");
