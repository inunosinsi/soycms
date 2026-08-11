<?php

class CreatePage extends WebPage{

	var $templateId;
	var $templateName;
	var $selected = "list";
	var $templatePath;
	var $iniPath;

	function doPost(){

		$template = $_POST["Template"];
		$templateId = htmlspecialchars($template["id"]);
		$this->templateId = mb_convert_kana($templateId, "a");
		$this->templateName = htmlspecialchars($template["name"]);
		$this->selected = $template["type"];
		if(strlen($this->templateName) < 1) $this->templateName = $this->templateId;

		$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/" . $this->selected."/";
		if(!file_exists($templateDir)) mkdir($templateDir);

		$this->templatePath = $templateDir . $this->templateId . ".html";
		$this->iniPath =$templateDir . $this->templateId . ".ini";

		//同じファイル名があった場合はエラーを返す
		if(!array_search($this->templateId . ".html", scandir($templateDir))){
			if(preg_match('/^[a-zA-Z0-9\._]+$/', $this->templateId)){
				file_put_contents($this->templatePath, "");
				file_put_contents($this->iniPath, "name= \"" . $this->templateName . "\"");

				SOY2PageController::jump("Site.Template");
			}
		}
	}

    function __construct() {

    	$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

    	parent::__construct();

    	if(isset($_GET["type"])) $this->selected = $_GET["type"];
    	if($this->templateId) DisplayPlugin::visible("failed");

    	if(!isset($this->templateId) && isset($_GET["carrier"])) $this->templateId = $_GET["carrier"] . "_";

    	$this->addForm("create_form");

    	$this->buildForm();
    }

    function buildForm(){

    	$this->addInput("template_id", array(
    		"name" => "Template[id]",
    		"value" => $this->templateId
    	));

    	$this->addInput("template_name", array(
			"name" => "Template[name]",
			"value" => $this->templateName
    	));

    	$this->createAdd("template_type_list", "_common.Site.TemplateTypeListComponent", array(
			"list" => $this->getTemplateTypeList(),
			"selected" => $this->selected
		));
    }

	function getTemplateTypeList(){
		$list = array(
			SOYShop_Page::TYPE_LIST => "商品一覧ページ",
			SOYShop_Page::TYPE_DETAIL => "商品詳細ページ",
			SOYShop_Page::TYPE_FREE => "フリーページ",
			SOYShop_Page::TYPE_COMPLEX => "ナビゲーションページ",
			SOYShop_Page::TYPE_SEARCH => "検索結果ページ",
		);

		if(soyshop_get_mypage_id() == "none"){
			$list[SOYShop_Page::TYPE_MEMBER] = "会員詳細ページ";
		}

		return $list;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("テンプレートの追加", array("Site.Template" => "テンプレート管理"));
	}
}
