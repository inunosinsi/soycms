<?php
/**
 * @class Site.Page.IndexPage
 * @date 2009-11-16T19:23:12+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	function __construct(){
		//ページの初期化
		if(soy2_check_token()){
			SOY2Logic::createInstance("logic.site.page.PageCreateLogic")->initPageByIniFile();
			SOY2PageController::jump("Site.Pages?updated");
		}

		parent::__construct();

		$pageLogic = SOY2Logic::createInstance("logic.site.page.PageLogic");

		SOY2::import("domain.site.SOYShop_Page");
		$this->addActionLink("page_ini_button", array(
			"link" => SOY2PageController::createLink("Site.Pages"),
			"visible" => (SOYShop_Page::checkPageListFile()),
			"onclick" => "return confirm('ページ一覧を初期化します。よろしいですか？');"
		));

		$this->createAdd("page_type_list", "_common.PagePluginTypeListComponent", array(
			"list" => $pageLogic->getPageListByType(),
		));

		DisplayPlugin::toggle("no_page", $pageLogic->countPage() === 0);
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ページ管理");
	}
}
