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

		$this->createAdd("detail_page_link","HTMLLink", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->id)
		));

		$this->buildForm();
    }

    function buildForm(){

    	$logic = SOY2Logic::createInstance("logic.site.page.PageLogic");
		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

		try{
			$page = $dao->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Site.Pages");
			exit;
		}

		$obj = $page->getPageObject();
		$this->page = $page;

		$this->createAdd("page_name","HTMLLabel", array(
			"text" => $page->getName()
		));

		$this->createAdd("update_form","HTMLForm");

		$this->createAdd("type_id","HTMLCheckbox", array(
			"name" => "Page[sortType]",
			"selected" => ($obj->getSortType()=="id"),
			"value" => "id",
			"label" => "ID",
		));

		$this->createAdd("type_item_code","HTMLCheckbox", array(
			"name" => "Page[sortType]",
			"selected" => ($obj->getSortType()=="item_code"),
			"value" => "item_code",
			"label" => "商品コード",
		));

    	$this->createAdd("sort_normal","HTMLCheckbox", array(
			"name" => "Page[sortOrder]",
			"selected" => (!$obj->getSortOrder()),
			"value" => 0,
			"label" => "昇順",
		));

		$this->createAdd("sort_reverse","HTMLCheckbox", array(
			"name" => "Page[sortOrder]",
			"selected" => ($obj->getSortOrder()),
			"value" => 1,
			"label" => "降順",
		));
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
