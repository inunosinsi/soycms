<?php
/**
 * @class Site.Config.CategoryPage
 * @date 2009-11-26T18:15:15+09:00
 * @author SOY2HTMLFactory
 */
class CategoryPage extends WebPage{

	function doPost(){

		if(soy2_check_token()){
			self::saveConfig($_POST["Config"]);
		}

		SOY2PageController::jump("Site.Config.Category");
	}

	function __construct(){
		parent::__construct();

		$this->addForm("update_form");

		$this->buildForm();
	}

	function buildForm(){
		$categories = soyshop_get_category_objects();

		$this->createAdd("category_tree","MyTree", array(
			"list" => $categories
		));

		try{
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByType(SOYShop_Page::TYPE_LIST);
		}catch(Exception $e){
			$pages = array();
		}


		$this->createAdd("category_detail_list", "_common.Site.Config.CategoryDetailListComponent", array(
			"list" => $categories,
			"pages" => $pages,
			"config" => self::getConfig()
		));
	}

	private function getConfig(){
		return SOYShop_DataSets::get("common.category_navigation", array());
	}

	private function saveConfig($array){
		SOYShop_DataSets::put("common.category_navigation", $array);
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.pack.js",
		);
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.css",
			$root . "tree.css",
		);
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カテゴリツリー設定");
	}
}

SOY2HTMLFactory::importWebPage("_base.TreeComponent");
class MyTree extends TreeComponent{

	function getOnclick($id){
		return 'onClickLeaf('.$id.');';
	}

	function getClass($id){
		return "";
	}
}
