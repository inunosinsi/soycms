<?php

class AsyncCartButtonConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.async_cart_button.util.AsyncCartButtonUtil");
	}
	
	function doPost(){

		if(soy2_check_token()){
			AsyncCartButtonUtil::savePageDisplayConfig($_POST["display_config"]);
			$this->configObj->redirect("updated");
		}
	}
	
	function execute(){
		parent::__construct();
		
		$this->addForm("form");
		
		SOY2::import("module.plugins.async_cart_button.component.PageListComponent");
		$this->createAdd("page_list", "PageListComponent", array(
			"list" => self::getPageList(),
			"displayConfig" => AsyncCartButtonUtil::getPageDisplayConfig()
		));
		
		$this->addImage("img_cart", array(
			"src" => "/" . SOYSHOP_ID . "/themes/sample/soyshop_async_add_item.png"
		));
	}
	
	private function getPageList(){
		try{
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			return array();	
		}
		
		$list = array();
		foreach($pages as $page){
			if(is_null($page->getId())) continue;
			$list[$page->getId()] = $page->getName();
		}
		$list[AsyncCartButtonUtil::PAGE_TYPE_CART] = "カートページ";
		$list[AsyncCartButtonUtil::PAGE_TYPE_MYPAGE] = "マイページ";
		
		return $list;
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>