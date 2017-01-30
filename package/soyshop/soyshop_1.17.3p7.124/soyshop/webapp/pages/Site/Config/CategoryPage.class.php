<?php
/**
 * @class Site.Config.CategoryPage
 * @date 2009-11-26T18:15:15+09:00
 * @author SOY2HTMLFactory
 */
class CategoryPage extends WebPage{

	function doPost(){

		if(soy2_check_token()){

			$this->saveConfig($_POST["Config"]);
		}

		SOY2PageController::jump("Site.Config.Category");
	}

	function __construct(){
		WebPage::__construct();

		$this->addForm("update_form");

		$this->buildForm();
	}

	function buildForm(){
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$array = $dao->get();

		$this->createAdd("category_tree","MyTree", array(
			"list" => $array
		));

		$pageDAO = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$pages = $pageDAO->getByType(SOYShop_Page::TYPE_LIST);

		$this->createAdd("category_detail_list", "_common.Site.Config.CategoryDetailListComponent", array(
			"list" => $array,
			"pages" => $pages,
			"config" => $this->getConfig()
		));
	}

	function getConfig(){
		return SOYShop_DataSets::get("common.category_navigation", array());
	}

	function saveConfig($array){
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
?>