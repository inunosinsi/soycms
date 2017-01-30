<?php

class ShopInfoAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
		$shopConfig = SOYShop_ShopConfig::load();
		
		$this->addLabel("shop_name", array(
			"text" => $shopConfig->getShopName()
		));

		$this->addLink("shop_url", array(
			"text" => soyshop_get_site_url(true),
			"link" => soyshop_get_site_url(true)
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>