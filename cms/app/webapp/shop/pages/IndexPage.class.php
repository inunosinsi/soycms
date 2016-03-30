<?php
/**
 * @class IndexPage
 * @date 2008-10-29T17:48:20+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends SOYShopWebPage{


	function IndexPage(){
		WebPage::WebPage();

		//soyshop site list
		$sites = $this->getSites();
		$this->createAdd("soyshop_list", "_common.SOYShop_SiteList",array(
			"list" => $sites,
			"logic" => SOY2Logic::createInstance("logic.ShopLogic")
		));

		$this->addModel("no_soyshop", array(
			"visible" => (count($sites) === 0)
		));

		$this->addModel("display_soyshop_list", array(
			"visible" => (count($sites) > 0)
		));

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