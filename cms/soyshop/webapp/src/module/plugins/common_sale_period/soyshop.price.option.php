<?php
/*
 */
class SalePeriodPriceOption extends SOYShopPriceOptionBase{

	const PLUGIN_ID = "common_sale_period";

	function doPost(SOYShop_Item $item){
		self::getPriceLogic()->save($item->getId());
	}

	function getTitle(SOYShop_Item $item){
		return "セール期間";
	}

	function getForm(SOYShop_Item $item){
		$html = array();

		$obj = self::getPriceLogic()->get($item->getId());

		$html[] = "<input type=\"text\" name=\"" . self::PLUGIN_ID . "_start\" id=\"sale_period_start\" class=\"date_picker_start\" value=\"" . soyshop_convert_date_string($obj->getSalePeriodStart()) . "\" style=\"width:30%;\" readonly>～";
		$html[] = "<input type=\"text\" name=\"" . self::PLUGIN_ID . "_end\" id=\"sale_period_end\" class=\"date_picker_end\" value=\"" . soyshop_convert_date_string($obj->getSalePeriodEnd()) . "\" style=\"width:30%;\" readonly>";
		$html[] = " <a href=\"javascript:void(0)\" id=\"period_clear_button\" class=\"btn btn-default\">クリア</a>";
		$html[] = "<br>※セール期間を設定する場合はセール中に設定するにチェックを入れてください。";
		$html[] = "<script>" . file_get_contents(dirname(__FILE__) . "/js/script.js") . "</script>";

		return implode("\n", $html);
	}

	private $logic;

	private function getPriceLogic(){
		if(!$this->logic) $this->logic = SOY2Logic::createInstance("module.plugins.". self::PLUGIN_ID . ".logic.PriceLogic");
		return $this->logic;
	}
}
SOYShopPlugin::extension("soyshop.price.option", "common_sale_period", "SalePeriodPriceOption");
