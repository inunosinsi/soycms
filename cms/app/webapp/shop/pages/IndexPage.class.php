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
		$sites = $this->getSites();
		$this->createAdd("soyshop_list", "_common.SOYShop_SiteList",array(
			"list" => $sites,
			"logic" => SOY2Logic::createInstance("logic.ShopLogic")
		));

		DisplayPlugin::toggle("no_soyshop", (count($sites) === 0));
		DisplayPlugin::toggle("is_soyshop", (count($sites) > 0));
	}

	/**
	 * get SOY Shop Sites
	 * @return Array Site
	 */
	function getSites(){
		$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
		try{
			$sites = $dao->get();
		}catch(Exception $e){
			$sites = array();
		}
		return $sites;
	}
}
?>
