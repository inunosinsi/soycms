<?php
/**
 * @class Site.Template.EditorPage
 * @date 2009-11-27T03:36:27+09:00
 * @author SOY2HTMLFactory
 */
class EditorPage extends WebPage{

	private $filepath;
	private $iniFilePath;
	private $value;
	private $cmsJsDirPath;

	function doPost(){

		if(soy2_check_token()){
			file_put_contents($this->iniFilepath,"name= \"" . $_POST["template_name"] . "\"");
			file_put_contents($this->filepath,$_POST["template_content"]);
		}

		$url = self::getRedirectUrl() . "?updated";
		if(isset($_GET["id"])) $url .= "&id=" . (int)($_GET["id"]);

		SOY2PageController::redirect($url);

	}

	function __construct($args){

		if(!defined("SOYCMS_ADMIN_URI")) define("SOYCMS_ADMIN_URI", "soycms");
		if(!defined("SOYSHOP_ADMIN_URI")) define("SOYSHOP_ADMIN_URI", "soyshop");
		$this->cmsJsDirPath = str_replace("/" . SOYSHOP_ADMIN_URI . "/", "/" . SOYCMS_ADMIN_URI . "/", SOY2PageController::createRelativeLink("./js/"));

		SOY2::import("domain.site.SOYShop_Page");

		$value = implode("/", $args);
		$this->value = $value;

		//フォームのHTMLの外出し
		if(isset($_GET["create"])){
			$appId = str_replace(".html", "", $args[1]);
			$createLogic = SOY2Logic::createInstance("logic.site.template.TemplateCreateLogic");
			$createLogic->copyTemplates($args[0], $appId);
			$url = self::getRedirectUrl() . "?success";
			SOY2PageController::redirect($url);
		}

		$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/";
		$filepath = $templateDir . $value;
		$iniFilepath = str_replace(".html", ".ini", $filepath);
		$file = basename($filepath);
		$dir = dirname($filepath) . "/";

		$this->filepath = $filepath;
		$this->iniFilepath = $iniFilepath;

		parent::__construct();

		DisplayPlugin::toggle("success", (isset($_GET["success"])));

		if(preg_match('/(.*)\.html$/', $file, $tmp)){

			if(file_exists($dir . $tmp[1] . ".ini")){
				$array = parse_ini_file($dir . $tmp[1] . ".ini");
			}
		}

		if(isset($_GET["id"])){
			try{
				$pageDAO = SOY2DAOFactory::create("site.SOYShop_PageDAO");
				$page = $pageDAO->getById($_GET["id"]);
				$array = array(
					"id" => $page->getId(),
					"name" => $page->getName()
				);
			}catch(Exception $e){
				//
			}
		}

		$this->addLink("page_link", array(
			"link" => (isset($array["id"])) ? SOY2PageController::createLink("Site.Pages.Detail." . $array["id"]) : "",
		));
		DisplayPlugin::toggle("custom_template", isset($array["id"]));

		$this->addForm("update_form");

		$this->addInput("template_name_input", array(
			"name" => "template_name",
			"value" => (isset($array["name"])) ? $array["name"] : ""
		));

		$this->addLink("copy_template", array(
			"link" => SOY2PageController::createLink("Site.Template.Editor.-.") . $value . "?create",
			"visible" => (isset($_GET["developer"]) && ($args[0] == SOYShop_Page::TYPE_CART || $args[0] == SOYShop_Page::TYPE_MYPAGE))
		));

		//template保存のボタン追加
    	$this->addLink("save_template_button", array(
    		"link" => "javascript:void(0);",
    		"id" => "save_template_button",
			"onclick" => "javascript:save_ajax('" . SOY2PageController::createLink("Site.Template.SaveTemplate.-."). $value . "',this);",
    		//"onclick" => "javascript:save_template('" . SOY2PageController::createLink("Site.Template.SaveTemplate.-."). $value . "',this);",
    		"visible" => function_exists("json_encode")
    	));

		$this->addLabel("template_path", array(
			"text" => (strlen($value)) ? SOYSHOP_SITE_DIRECTORY . ".template/" . $value : ""
		));

		$this->addLabel("template_name", array(
			"text" => (isset($array["name"])) ? $array["name"] : ""
		));

		// $this->addTextArea("template_content", array(
		// 	"name" => "template_content",
		// 	"value" => file_get_contents($filepath)
		// ));

		$this->addLabel("template_content_ace", array(
			"text" => file_get_contents($filepath)
		));

		/** タグサンプル **/
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		if($config->getDisplayUsableTagList()){
			SOY2::import("module.CMSTagManager");
			CMSTagManager::addTemplateType($args[0]);
			$tagList = CMSTagManager::get();
		}else{
			$tagList = array();
		}

		DisplayPlugin::toggle("show_tag_sample_list", count($tagList));

		$this->createAdd("tag_sample_list", "_common.Site.TemplateTagSampleComponent", array(
			"list" => $tagList
		));

		//ace editor
		$this->addModel("ace_editor", array(
			"attr:src" => $this->cmsJsDirPath . "ace/ace.js"
		));
	}

	private function getRedirectUrl(){
		return SOY2PageController::createLink("Site.Template.Editor") . "/-/" . $this->value;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("テンプレートの編集", array("Site.Template" => "テンプレート管理"));
	}
}
