<?php
class SalePeriodCustomField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "common_sale_period";

	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		if(!is_null($item->getId())){
			$onSale = self::getPriceLogic()->checkOnSale($item);
			$price = self::getPriceLogic()->getDisplayPrice($item);
		}else{
			$onSale = false;
			$price = 0;
		}

		$htmlObj->addModel("on_sale_extend", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($onSale)
		));

		$htmlObj->addModel("not_on_sale_extend", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (!$onSale)
		));

		$htmlObj->addLabel("item_price_extend", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => soyshop_display_price($price)
		));

		$htmlObj->createAdd("sale_start_date", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => self::getPriceLogic()->getSaleDate($item->getId(), "start"),
			"defaultFormat"=>"Y-m-d"
		));

		$htmlObj->createAdd("sale_end_date", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => self::getPriceLogic()->getSaleDate($item->getId(), "end"),
			"defaultFormat"=>"Y-m-d"
		));
	}

	private $logic;

	private function getPriceLogic(){
		if(!$this->logic) $this->logic = SOY2Logic::createInstance("module.plugins.". self::PLUGIN_ID . ".logic.PriceLogic");
		return $this->logic;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_sale_period", "SalePeriodCustomField");
