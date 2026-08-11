<?php
/**
 * @class Site.Pages.Extra.DetailPage
 * @date 2009-12-04T02:01:25+09:00
 * @author SOY2HTMLFactory
 */
class DetailPage extends WebPage{

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

			SOY2PageController::jump("Site.Pages.Extra.Detail." . $this->id."?updated");
			exit;
		}
	}

    function __construct($args) {
    	$this->id = $args[0];

    	parent::__construct();

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

		$this->addForm("update_form");

		$this->addCheckBox("type_id", array(
			"name" => "Page[sortType]",
			"selected" => ($obj->getSortType()=="id"),
			"value" => "id",
			"label" => "ID",
		));

		$this->addCheckBox("type_item_code", array(
			"name" => "Page[sortType]",
			"selected" => ($obj->getSortType()=="item_code"),
			"value" => "item_code",
			"label" => "商品コード",
		));

    	$this->addCheckBox("sort_normal", array(
			"name" => "Page[sortOrder]",
			"selected" => (!$obj->getSortOrder()),
			"value" => 0,
			"label" => "昇順",
		));

		$this->addCheckBox("sort_reverse", array(
			"name" => "Page[sortOrder]",
			"selected" => ($obj->getSortOrder()),
			"value" => 1,
			"label" => "降順",
		));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品詳細ページ設定", array("Site.Pages" => "ページ管理", "Site.Pages.Detail." . $this->id => "ページ設定"));
	}

	function getSubMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Site.Pages.SubMenu.SubMenuPage", array(
				"arguments" => array($this->id,$this->page)
			))->getObject();
		}catch(Exception $e){
			return null;
		}
	}
}
