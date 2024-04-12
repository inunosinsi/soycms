<?php

class OrderInvoiceCommon{

	public static function getFileDirectory(){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/invoice/";
		if(!is_dir($dir)) mkdir($dir);

		return $dir;
	}

	public static function getFileUrl(){
		return SOYSHOP_SITE_URL . "files/invoice/";
	}

	public static function getConfig(){
		return SOYShop_DataSets::get("order_invoice.config", array(
			"logo" => "",		//ロゴ画像名
			"stamp" => "",		//社印名
			"title" => "お店からのお便り",
			"content" => "",
			"payment" => 0,	//振込先情報を表示するか？,
			"firstOrder" => 1	//初回購入であることの表示
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("order_invoice.config", $values);
	}

	public static function getTemplateName(){
		return SOYShop_DataSets::get("order_invoice.template", "default");
	}

	public static function saveTemplateName($template){
		SOYShop_DataSets::put("order_invoice.template", $template);
	}

	public static function getTemplateList(){
		$files = array();
		if ($dir = opendir(dirname(dirname(__FILE__)) . "/template")) {
			while(($file = readdir($dir)) !== false){
				if($file != "." && $file != ".." && strpos($file, ".html") > 0){
					$files[] = str_replace(".html", "", $file);
				}
			}
			closedir($dir);
		}

		return $files;
	}

	public static function checkReducedTaxRateMode(SOYShop_Order $order){
		$attrList = $order->getAttributeList();
		return (isset($attrList["reduced_tax_rate_mode"]));
	}

	public static function calcReducedTaxRateTargetItemTotal($itemOrders){
		if(!count($itemOrders)) return 0;
		$taxLogic = SOY2Logic::createInstance("module.plugins.common_consumption_tax.logic.CalculateTaxLogic");

		$total = 0;
		foreach($itemOrders as $itemOrder){
			if(is_null($itemOrder->getItemId()) || !$taxLogic->isReducedTaxRateItem($itemOrder->getItemId())) continue;
			$total += (int)$itemOrder->getTotalPrice();
		}
		return $total;
	}
}
