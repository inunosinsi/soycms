<?php
/**
 * @class Site.Page.IndexPage
 * @date 2009-11-16T19:23:12+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	//全ページリスト
	private $all = array();

	function __construct(){
		//ページの初期化
		if(soy2_check_token()){
			SOY2Logic::createInstance("logic.site.page.PageCreateLogic")->initPageByIniFile();
			SOY2PageController::jump("Site.Pages?updated");
		}

		parent::__construct();

		//すべてのページを取得
		$this->all = self::_getPages();
		$pageCount = (count($this->all));

		$this->addActionLink("page_ini_button", array(
			"link" => SOY2PageController::createLink("Site.Pages"),
			"visible" => (SOYShop_Page::checkPageListFile()),
			"onclick" => "return confirm('ページ一覧を初期化します。よろしいですか？');"
		));

		$this->createAdd("page_type_list", "_common.PagePluginTypeListComponent", array(
			"list" => self::_getPageList(),
		));

		DisplayPlugin::toggle("no_page", $pageCount === 0);
	}

	private function _getPages(){
		$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		if(!count($pages)) return array();

		$res = array();
		foreach($pages as $page){
			$res[$page->getUri()] = $page;
		}

		ksort($res);
		return $res;
	}

	//タイプ別のページリストを取得
	private function _getPageList(){

		$configs = array();
		$list = array();

		//多言語化サイトプラグインがアクティブの時
		if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			if(class_exists("UtilMultiLanguageUtil")){
				$multiLangConfig = UtilMultiLanguageUtil::getConfig();

				foreach($multiLangConfig as $key => $values){
					if(
						(isset($values["prefix"]) && strlen($values["prefix"])) &&
						(isset($values["is_use"]) && $values["is_use"] == UtilMultiLanguageUtil::IS_USE)
					){
						$configs[$key] = $values["prefix"];
					}
				}
			}
		}

		//携帯自動振り分けプラグインがアクティブの時
		if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
			SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
			if(class_exists("UtilMobileCheckUtil")){
				$mobileCheckConfig = UtilMobileCheckUtil::getConfig();

				if(isset($mobileCheckConfig["prefix"]) && strlen($mobileCheckConfig["prefix"])){
					$configs["m"] = $mobileCheckConfig["prefix"];
				}

				if(isset($mobileCheckConfig["prefix_i"]) && strlen($mobileCheckConfig["prefix_i"])){
					$configs["i"] = $mobileCheckConfig["prefix_i"];
				}

				//多言語化サイトと併用
				if(SOYShopPluginUtil::checkIsActive("util_multi_language") && class_exists("UtilMultiLanguageUtil")){
					foreach($multiLangConfig as $key => $values){
						if(
							(isset($values["prefix"]) && strlen($values["prefix"])) &&
							(isset($values["is_use"]) && $values["is_use"] == UtilMultiLanguageUtil::IS_USE)
						){
							$configs[$configs["i"] . "/" . $key] = $configs["i"] . "/" . $values["prefix"];
						}
					}
				}
			}
		}

		krsort($configs);

		foreach($configs as $key => $prefix){
			foreach($this->all as $page){
				if(strpos($prefix, "/") === false){
					if($page->getUri() == $prefix || preg_match('/^' . $prefix . '\//', $page->getUri())){
						$list[$prefix][$page->getUri()] = $page;
						unset($this->all[$page->getUri()]);
					}
				}else{
					if($page->getUri() == $prefix || strpos($page->getUri(), $prefix . "/") === 0){
						$list[$prefix][$page->getUri()] = $page;
						unset($this->all[$page->getUri()]);
					}
				}
			}
		}

		ksort($list);

		//最後に並べ替え
		$pageList["jp"] = $this->all;
		foreach($list as $key => $values){
			$pageList[$key] = $values;
		}

		return $pageList;

	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ページ管理");
	}
}
