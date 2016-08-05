<?php
/**
 * @class Site.Template.IndexPage
 * @date 2009-11-16T19:36:01+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	private $templateLogic;
	private $types;

	function __construct(){
		WebPage::WebPage();
		
		$this->templateLogic = SOY2Logic::createInstance("logic.site.template.TemplateLogic");
		$this->types = SOYShop_Page::getTypeTexts();

		$this->buildTemplate();
		$this->buildModule();
		$this->buildHtmlModule();
	}
	
	function buildTemplate(){
		
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

		$custom = $this->getCustomTemplateList($dao->get());
		$this->createAdd("custom_template_list", "_common.Site.TemplateListComponent", array(
			"list" => $custom
		));

		DisplayPlugin::toggle("custom_template_list_empty", empty($custom));
		DisplayPlugin::toggle("custom_template_list_exists", !empty($custom));
	}
		
	function buildModule(){
		
		//PHPモジュールの使用が許可されているか？
		DisplayPlugin::toggle("allow_php_module", (defined("SOYCMS_ALLOW_PHP_MODULE") && SOYCMS_ALLOW_PHP_MODULE));
		
		//モジュール
		$modules = $this->getModules();
		
		DisplayPlugin::toggle("module_list_exists", (count($modules) > 0));
		DisplayPlugin::toggle("module_list_empty", (count($modules) < 1));

		$this->createAdd("module_list", "_common.Site.ModuleListComponent", array(
			"list" => $modules,
			"moduleType" => "php"
		));
	}
	
	function buildHtmlModule(){
		
		//モジュール
		$modules = $this->getHtmlModules();
	
		DisplayPlugin::toggle("html_module_list_exists", (count($modules) > 0));
		DisplayPlugin::toggle("html_module_list_empty", (count($modules) < 1));
	
		$this->createAdd("html_module_list","_common.Site.ModuleListComponent", array(
			"list" => $modules,
			"moduleType" => "html"
		));
	}

	function getCustomTemplateList($pages){
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
	
	function getModules(){
		$res = array();
		$moduleDir = SOYSHOP_SITE_DIRECTORY . ".module/";
		
		$files = soy2_scanfiles($moduleDir);
		
		foreach($files as $file){
			$moduleId  = str_replace($moduleDir, "", $file);
			if(!preg_match('/\.php$/', $file)) continue;
			if(!$this->checkModuleDir($moduleId)) continue;
			
			//一個目の/より前はカテゴリ
			$moduleId = preg_replace('/\.php$/', "", $moduleId);
			$moduleId = str_replace("/", ".", $moduleId);
			$name = $moduleId;
			
			//ini
			$iniFilePath = preg_replace('/\.php$/', ".ini", $file);
			if(file_exists($iniFilePath)){
				$array = parse_ini_file($iniFilePath);
				if(isset($array["name"])) $name = $array["name"];
			}
			
			$res[] = array(
				"name" => $name,
				"moduleId" => $moduleId,
			);	
		}
		
		return $res;
	}
	
	function getHtmlModules(){
		$res = array();
		$moduleDir = SOYSHOP_SITE_DIRECTORY . ".module/html/";
		
		$files = array();
		if(is_dir($moduleDir)){
			$files = soy2_scanfiles($moduleDir);
		}
		
		
		foreach($files as $file){
			$moduleId  = str_replace($moduleDir, "", $file);
			if(!preg_match('/\.php$/', $file)) continue;
	
			//一個目の/より前はカテゴリ
			$moduleId = preg_replace('/\.php$/', "", $moduleId);
			$moduleId = str_replace("/", ".", $moduleId);
			$name = $moduleId;
			
			//ini
			$iniFilePath = preg_replace('/\.php$/', ".ini", $file);
			if(file_exists($iniFilePath)){
				$array = parse_ini_file($iniFilePath);
				if(isset($array["name"]))$name = $array["name"];
			}
			
			$res[] = array(
				"name" => $name,
				"moduleId" => $moduleId,
			);	
		}
		
		return $res;
	}
	
	//モジュール群からcommonディレクトリにあるモジュールを除く
	function checkModuleDir($dir){
		$res = true;
		
		if(preg_match("/^common./", $dir)){
			$res = false;
		}
		if(preg_match("/^html./", $dir)){
			$res = false;
		}
		
		return $res;
	}
}
?>