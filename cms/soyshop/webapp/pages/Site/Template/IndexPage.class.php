<?php
/**
 * @class Site.Template.IndexPage
 * @date 2009-11-16T19:36:01+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	const TYPE_PHP = "php";
	const TYPE_HTML = "html";

	private $templateLogic;
	private $types;

	function __construct(){
		parent::__construct();

		$this->templateLogic = SOY2Logic::createInstance("logic.site.template.TemplateLogic");
		$this->types = SOYShop_Page::getTypeTexts();

		self::buildTemplate();
		self::buildModule();
		self::buildHtmlModule();
	}

	private function buildTemplate(){

		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

		$this->createAdd("template_carrier_list", "_common.Site.TemplateCarrierListComponent", array(
			"list" => $this->templateLogic->getTemplateList($this->types),
			"typeTexts" => $this->types
		));

		//アプリケーションテンプレート一覧ページのリンクの表示
		DisplayPlugin::toggle("show_app_tmp_link", $this->templateLogic->checkHasTempDir());

		$this->createAdd("cart_template_list", "_common.Site.TemplateListComponent", array(
			"list" => $this->templateLogic->getApplicationTemplateList(SOYShop_Page::TYPE_CART)
		));

		$this->createAdd("mypage_template_list", "_common.Site.TemplateListComponent", array(
			"list" => $this->templateLogic->getApplicationTemplateList(SOYShop_Page::TYPE_MYPAGE)
		));

		$custom = self::getCustomTemplateList($dao->get());
		$this->createAdd("custom_template_list", "_common.Site.TemplateListComponent", array(
			"list" => $custom
		));

		DisplayPlugin::toggle("custom_template_list_empty", empty($custom));
		DisplayPlugin::toggle("custom_template_list_exists", !empty($custom));
	}

	private function buildModule(){

		//PHPモジュールの使用が許可されているか？
		DisplayPlugin::toggle("allow_php_module", (defined("SOYCMS_ALLOW_PHP_MODULE") && SOYCMS_ALLOW_PHP_MODULE));

		//モジュール
		$modules = self::getModules();

		DisplayPlugin::toggle("module_list_exists", (count($modules) > 0));
		DisplayPlugin::toggle("module_list_empty", (count($modules) < 1));

		$this->createAdd("module_list", "_common.Site.ModuleListComponent", array(
			"list" => $modules,
			"moduleType" => "php"
		));
	}

	private function buildHtmlModule(){

		//モジュール
		$modules = self::getModules(self::TYPE_HTML);

		DisplayPlugin::toggle("html_module_list_exists", (count($modules) > 0));
		DisplayPlugin::toggle("html_module_list_empty", (count($modules) < 1));

		$this->createAdd("html_module_list","_common.Site.ModuleListComponent", array(
			"list" => $modules,
			"moduleType" => "html"
		));
	}

	private function getCustomTemplateList($pages){
		$res = array();

		foreach($pages as $page){
			if(file_exists($page->getCustomTemplateFilePath())){

				$res[$page->getId()] = array(
					"path" => $page->getCustomTemplateFilePath(false) . "?id=" . $page->getId(),
					"type" => $this->types[$page->getType()],
					"name" => $page->getName(),
					"url" => "/" . $page->getUri()
				);
			}
		}

		return $res;
	}

	private function getModules($t = self::TYPE_PHP){
		$res = array();
		$moduleDir = self::getModuleDirectory();

		$files = soy2_scanfiles($moduleDir);

		foreach($files as $file){
			if(!preg_match('/\.php$/', $file)) continue;

			$moduleId = preg_replace('/^.*\.module\//', "", $file);
			if($t == self::TYPE_PHP){
				if(!self::checkModuleDir($moduleId)) continue;
			}else{
				if(self::checkModuleDir($moduleId)) continue;
			}

			//一個目の/より前はカテゴリ
			$moduleId = preg_replace('/\.php$/', "", $moduleId);
			$moduleId = str_replace("/", ".", $moduleId);
			$name = $moduleId;

			//ini
			$iniFilePath = preg_replace('/\.php$/', ".ini", $file);
			if(file_exists($iniFilePath)){
				$array = @parse_ini_file($iniFilePath);
				if(isset($array["name"])) $name = $array["name"];
			}

			$res[] = array(
				"name" => $name,
				"moduleId" => $moduleId,
			);
		}

		return $res;
	}

	private function getModuleDirectory($t = self::TYPE_PHP){
		if(isset($t) && $t == self::TYPE_HTML){
			return SOYSHOP_SITE_DIRECTORY . ".module/html/";
		}else{
			return SOYSHOP_SITE_DIRECTORY . ".module/";
		}
	}

	//モジュール群からcommonディレクトリにあるモジュールを除く
	private function checkModuleDir($dir){
		return (preg_match("/^common./", $dir) || preg_match("/^html./", $dir)) ? false : true;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("テンプレート管理");
	}
}
