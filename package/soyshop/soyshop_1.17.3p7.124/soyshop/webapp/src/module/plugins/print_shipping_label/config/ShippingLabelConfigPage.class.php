<?php

class ShippingLabelConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2DAOFactory::importEntity("config.SOYShop_Area");
		SOY2::import("module.plugins.print_shipping_label.util.ShippingLabelUtil");
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("domain.plugin.SOYShop_PluginConfig");
		SOY2::import("util.SOYShopPluginUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
						
			if(isset($_POST["LabelConfig"])){
				ShippingLabelUtil::saveConfig($_POST["LabelConfig"]);
			}
			
			$this->configObj->redirect("updated");
		}
	}
	
	function execute(){
		WebPage::__construct();
		
		//このプラグインを動かすために必要なプラグインがインストールされているか？
		DisplayPlugin::toggle("notice_no_installed_payment_daibiki", !SOYShopPluginUtil::checkIsActive("payment_daibiki"));
		DisplayPlugin::toggle("notice_no_installed_delivery_normal", !SOYShopPluginUtil::checkIsActive("delivery_normal"));
		
		$this->addForm("form");
				
		self::buildPrintForm();
	}
		
	private function buildPrintForm(){
		$config = ShippingLabelUtil::getConfig();
		
		$this->addCheckBox("shipping_date", array(
			"name" => "LabelConfig[shipping_date]",
			"value" => 1,
			"selected" => (isset($config["shipping_date"]) && $config["shipping_date"] == 1),
			"label" => "出力ボタンの日付フォームに明日の日付を入れておく"
		));
		
		$this->addTextArea("product_name", array(
			"name" => "LabelConfig[product]",
			"value" => (isset($config["product"])) ? $config["product"] : "",
			"style" => "width:300px;height:80px;"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}