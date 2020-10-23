<?php
/**
 * @class Site.Pages.CreatePage
 * @date 2009-11-17T17:11:26+09:00
 * @author SOY2HTMLFactory
 */
class CreatePage extends WebPage{

	function doPost(){

		if(soy2_check_token()){
			$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

			$obj = (object)$_POST["Page"];
			$obj = SOY2::cast("SOYShop_Page", $obj);

			$logic = SOY2Logic::createInstance("logic.site.page.PageCreateLogic");

			if($logic->validate($obj)){
				$id = $logic->create($obj);
				SOY2PageController::jump("Site.Pages.Detail." . $id . "?updated=created");
				exit;
			}

			$this->obj = $obj;
			$this->errors = $logic->getErrors();
		}
	}

	var $obj;
	var $errors = array();

	function __construct(){
		parent::__construct();

		$this->addForm("create_form");

		$this->buildForm();
	}


	function buildForm(){
		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$obj = ($this->obj) ? $this->obj : $this->getNewPageObject();

		//携帯自動振り分けプラグインや多言語化サイトプラグインから作成するページの場合はuriを予め入れておく
		if(preg_match('/^newpage_(.*).html/', $obj->getUri()) && isset($_GET["uri"])){
			$obj->setUri($_GET["uri"] . "/");
		}

		//サイトの管理トップからの遷移
		if(isset($_GET["type"])){
			if($_GET["type"] == "list") $obj->setType(SOYShop_Page::TYPE_LIST);
			if($_GET["type"] == "detail") $obj->setType(SOYShop_Page::TYPE_DETAIL);
			if($_GET["type"] == "free") $obj->setType(SOYShop_Page::TYPE_FREE);
			if($_GET["type"] == "complex") $obj->setType(SOYShop_Page::TYPE_COMPLEX);
			if($_GET["type"] == "search") $obj->setType(SOYShop_Page::TYPE_SEARCH);
		}

		$this->addInput("name", array(
			"name" => "Page[name]",
			"value" => $obj->getName()
		));

		$this->addInput("uri", array(
			"name" => "Page[uri]",
			"value" => $obj->getUri()
		));

		$this->createAdd("page_type_list", "_common.PageTypeListComponent", array(
			"list" => $this->getPageTypeList(),
			"selected" => $obj->getType()
		));

		//error
		foreach(array("name", "uri", "type") as $key){
			$this->addLabel("error_$key", array(
				"text" => (isset($this->errors[$key])) ? $this->errors[$key] : "",
				"visible" => (isset($this->errors[$key]) && strlen($this->errors[$key]))
			));
		}
	}

	function getPageTypeList(){
		$list = array(
			SOYShop_Page::TYPE_LIST => "商品一覧ページ",
			SOYShop_Page::TYPE_DETAIL => "商品詳細ページ",
			SOYShop_Page::TYPE_FREE => "フリーページ",
			SOYShop_Page::TYPE_COMPLEX => "ナビゲーションページ",
			SOYShop_Page::TYPE_SEARCH => "検索結果ページ"
		);

		if(soyshop_get_mypage_id() == "none"){
			$list[SOYShop_Page::TYPE_MEMBER] = "会員詳細ページ";
		}

		return $list;
	}

	function getNewPageObject(){
		$mapping = SOYShop_DataSets::get("site.url_mapping", array());
		$url = "newpage_" . count($mapping) . ".html";
		$obj = new SOYShop_Page();

		$obj->setUri($url);

		return $obj;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ページの作成", array("Site.Pages" => "ページ管理"));
	}
}
