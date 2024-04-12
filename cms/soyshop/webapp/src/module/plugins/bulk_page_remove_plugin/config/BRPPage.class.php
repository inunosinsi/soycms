<?php

class BRPPage extends WebPage{

	private $configObj;

	function __construct() {}

	function doPost(){
		if(AUTH_OPERATE && soy2_check_token()){

			if(isset($_POST["pages"]) && is_array($_POST["pages"]) && count($_POST["pages"])){
				$removeLogic = SOY2Logic::createInstance("logic.site.page.PageRemoveLogic");
				SOYShopPlugin::load("soyshop.page.update");

				foreach($_POST["pages"] as $pageId){
					$removeLogic->remove($pageId);
					SOYShopPlugin::invoke("soyshop.page.update", array(
						"deletePageId" => $pageId
					));
				}
			}

			if(isset($_POST["templates"]) && is_array($_POST["templates"]) && count($_POST["templates"])){
				$tmpDir = SOYSHOP_SITE_DIRECTORY . ".template/";

				foreach($_POST["templates"] as $path){
					$tmpPath = $tmpDir.$path;
					$iniPath = str_replace(".html", ".ini", $tmpPath);

					try{
						unlink($tmpPath);
						unlink($iniPath);
					}catch(Exception $e){
			
					}
				}
			}

			SOYShopCacheUtil::clearCache();

			$this->configObj->redirect("removed");
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("removed", isset($_GET["removed"]));
		DisplayPlugin::toggle("annotation", !AUTH_OPERATE);

		$pageLogic = SOY2Logic::createInstance("logic.site.page.PageLogic");

		$this->addForm("form");

		$this->createAdd("page_type_list", "_common.PagePluginTypeListComponent", array(
			"list" => self::_removeNotFoundPage($pageLogic->getPageListByType()),
		));


		/** ページに紐付いていないテンプレートを表示する **/
		$tmps = self::_getUnusedTemplateList();
		DisplayPlugin::toggle("template_list", count($tmps));

		$this->createAdd("template_list", "_common.Site.TemplateListComponent", array(
			"list" => $tmps
		));
	}

	/**
	 * ページリストから404ページ分を除く
	 * @param array
	 * @return array
	 */
	private function _removeNotFoundPage(array $pageListByType){
		$_arr = array();

		foreach($pageListByType as $lng => $pages){
			foreach($pages as $uri => $page){
				if($uri == SOYSHOP_404_PAGE_MARKER) continue;
				$_arr[$lng][$uri] = $page;
			}
		}

		return $_arr;
	}

	function _getUnusedTemplateList(){
		try{
			$res = soyshop_get_hash_table_dao("page")->executeQuery(
				"SELECT type, template FROM soyshop_page"
			);
		}catch(Exception $e){
			$res = array();
		}

		$usedTemplateList = array();
		if(count($res)){
			foreach($res as $v){
				$usedTemplateList[] = $v["type"]."/".$v["template"];
			}
		}
		
		$templateListByLang = SOY2Logic::createInstance("logic.site.template.TemplateLogic")->getTemplateList(SOYShop_Page::getTypeTexts());
		if(!count($templateListByLang)) return array();

		$_arr = array();
		foreach($templateListByLang as $lang => $templateListByType){
			foreach($templateListByType as $typ => $templates){
				foreach($templates as $template){
					if(!isset($template["path"])) continue;
					if(is_numeric(array_search($template["path"], $usedTemplateList))) continue;

					$_arr[] = $template;
				}
			}
		}
		
		return $_arr;

	}

	function setConfigObj(BulkPageRemoveConfig $configObj) {
		$this->configObj = $configObj;
	}
}