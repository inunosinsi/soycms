<?php
/**
 * @class Site.Pages.Extra.SearchPage
 * @date 2009-11-19T22:42:23+09:00
 * @author SOY2HTMLFactory
 */
class SearchPage extends WebPage{

	function doPost(){

		if(soy2_check_token()){

			$array = $_POST["Page"];

			$logic = SOY2Logic::createInstance("logic.site.page.PageLogic");
			$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

			try{
				$page = $dao->getById($this->id);
			}catch(Exception $e){
				SOY2PageController::jump("Site.Pages");
				exit;
			}

			$obj = $page->getPageObject();
			SOY2::cast($obj,(object)$array);

			$page->setPageObject($obj);
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.Search." . $this->id."?updated");
			exit;
		}

	}

	var $id;
	var $page;

	function __construct($args){
		$this->id = (isset($args[0])) ? (int)$args[0] : null;

		parent::__construct();

		$this->addLink("detail_page_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->id)
		));

		$this->addForm("update_form");

		$this->buildForm();
	}

	function buildForm(){
		$page = soyshop_get_page_object($this->id);
		if(is_null($page->getId())) SOY2PageController::jump("Site.Pages");

		$obj = $page->getPageObject();
		$this->page = $page;

		$this->addLabel("page_name", array(
			"text" => $page->getName()
		));

		//search module
		$modules = $this->getModules();
		$this->addSelect("search_module_select", array(
			"name" => "Page[module]",
			"options" => $modules,
			"property" => "name",
			"selected" => $obj->getModule()
		));

		//display count
		$this->addInput("display_count", array(
			"name" => "Page[displayCount]",
			"value" => (is_numeric($obj->getDisplayCount())) ? $obj->getDisplayCount() : 10
		));

		/* sort */
		$this->createAdd("sort_list", "HTMLList", array(
			"list" => array(
				"name" => "商品名",
				"code" => "商品コード",
				"stock" => "在庫数",
				"price" => "販売価格",
				"cdate" => "作成日",
				"udate" => "更新日"
			),
			'populateItem:function($entity,$key)' =>
					'$this->createAdd("sort_input","HTMLCheckbox", array(' .
						'"name" => "Page[defaultSort]",' .
						'"value" => $key,' .
						'"label" => $entity,' .
						'"selected" => ($key == "'.$obj->getDefaultSort().'")' .
					'));'
		));

		$this->addCheckBox("sort_custom", array(
			"name" => "Page[defaultSort]",
			"value" => "_custom",
			"selected" => ($obj->getDefaultSort() == "_custom"),
			"label" => "カスタム項目でソート"
		));

		//ソートで使用するカスタム項目
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load(true);
		$indexed = SOYShop_ItemAttributeConfig::getIndexFields();
		$indexed = array_flip($indexed);
		$indexed = array_intersect_key($config, $indexed);
		$this->addSelect("sort_custom_field_list", array(
			"name" => "Page[customSort]",
			"selected" => $obj->getCustomSort(),
			"options" =>$indexed,
			"property" => "label"
		));

		$this->addCheckBox("sort_normal", array(
			"name" => "Page[isReverse]",
			"selected" => (!$obj->getIsReverse()),
			"value" => 0,
			"label" => "昇順",
		));

		$this->addCheckBox("sort_reverse", array(
			"name" => "Page[isReverse]",
			"selected" => ($obj->getIsReverse()),
			"value" => 1,
			"label" => "降順",
		));
	}

	/**
	 * 検索モジュールの配列を取得
	 */
	function getModules(){

		SOYShopPlugin::load("soyshop.search");
		$delegator = SOYShopPlugin::invoke("soyshop.search", array(
			"page" => $this->page
		));

		$list = $delegator->getList();
		$res = array();

		foreach($list as $key => $array){
			$res[$key] = $array["name"];
		}
		return $res;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("検索ページ設定", array("Site.Pages" => "ページ管理", "Site.Pages.Detail." . $this->id => "ページ設定"));
	}

	function getSubMenu(){
		$key = "Site.Pages.SubMenu.SubMenuPage";

		try{
			$subMenuPage = SOY2HTMLFactory::createInstance($key, array(
				"arguments" => array($this->id,$this->page)
			));
			return $subMenuPage->getObject();
		}catch(Exception $e){
			return null;
		}
	}
}
