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
			"list" => self::_getPageList(),
			"displayConfig" => AsyncCartButtonUtil::getPageDisplayConfig()
		));

		$this->addImage("img_cart", array(
			"src" => "/" . SOYSHOP_ID . "/themes/sample/soyshop_async_add_item.png"
		));
	}

	private function _getPageList(){
		$list = soyshop_get_page_list();
		$list[AsyncCartButtonUtil::PAGE_TYPE_CART] = "カートページ";
		$list[AsyncCartButtonUtil::PAGE_TYPE_MYPAGE] = "マイページ";

		return $list;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
