<?php

class ShopInfoAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->addLabel("shop_name", array(
			"text" => SOYShop_ShopConfig::load()->getShopName()
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
