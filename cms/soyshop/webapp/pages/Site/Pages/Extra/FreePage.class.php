<?php
/**
 * @class Site.Pages.Extra.FreePage
 * @date 2009-11-19T22:42:23+09:00
 * @author SOY2HTMLFactory
 */
class FreePage extends WebPage{

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
			$obj->setUpdateDate(time());

			$page->setPageObject($obj);
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.Free." . $this->id."?updated");
			exit;
		}

	}

	var $id;
	var $page;

	function __construct($args){
		$this->id = $args[0];

		parent::__construct();

		$this->addForm("update_form");

		$this->buildForm();
	}

	function buildForm(){
		$page = soyshop_get_page_object($this->id);
		if(is_null($page->getId())) SOY2PageController::jump("Site.Pages");

		$obj = $page->getPageObject();
		$this->page = $page;

		$this->createAdd("page_name","HTMLLabel", array(
			"text" => $page->getName()
		));

		$this->createAdd("title","HTMLInput", array(
			"name" => "Page[title]",
			"value" => $obj->getTitle(),
		));

		$this->createAdd("content","HTMLTextArea", array(
			"name" => "Page[content]",
			"value" => $obj->getContent(),
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("フリーページ設定", array("Site.Pages" => "ページ管理", "Site.Pages.Detail." . $this->id => "ページ設定"));
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
