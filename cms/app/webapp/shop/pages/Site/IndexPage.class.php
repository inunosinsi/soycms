<?php

class IndexPage extends SOYShopWebPage{

    function __construct() {
    	parent::__construct();

    	$this->createAdd("soyshop_list", "_common.SOYShop_SiteList", array(
    		"list" => ShopUtil::getSites(),
    		"logic" => SOY2Logic::createInstance("logic.RootLogic")
    	));
    }
}
