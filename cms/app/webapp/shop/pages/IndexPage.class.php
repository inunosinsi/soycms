<?php
/**
 * @class IndexPage
 * @date 2008-10-29T17:48:20+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends SOYShopWebPage{


	function __construct(){
		parent::__construct();

		//soyshop site list
		$sites = ShopUtil::getSites();
		$this->createAdd("soyshop_list", "_common.SOYShop_SiteList",array(
			"list" => $sites,
			"logic" => SOY2Logic::createInstance("logic.RootLogic")
		));

		$cnt = count($sites);
		DisplayPlugin::toggle("no_soyshop", ($cnt === 0));
		DisplayPlugin::toggle("is_soyshop", ($cnt > 0));
	}
}
