<?php
/**
 * @class Site.Pages.Extra.ComplexPage
 * @date 2010-02-11T23:11:52+09:00
 * @author SOY2HTMLFactory
 */
class ComplexPage extends WebPage{

	function doPost(){

		//check
		if(!soy2_check_token())return;

		$page = $this->page;
		$obj = $page->getPageObject();

		if(isset($_POST["add"])){
			$blockId = $_POST["blockId"];
			$blockId = $obj->addBlock($blockId);
			$page->setPageObject($obj);

			$logic = $this->getPageLogic();
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.ComplexPage." . $this->id."?created&blockId=" . $blockId);
			exit;
		}

		if(isset($_POST["remove"])){
			$obj->removeBlock($_POST["remove"]);
			$page->setPageObject($obj);

			$logic = $this->getPageLogic();
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.ComplexPage." . $this->id."?deleted");
			exit;
		}

		if(isset($_POST["Block"])){
			$blocks = $obj->getBlocks();

			foreach($_POST["Block"] as $blockId => $array){
				if(!isset($blocks[$blockId]))continue;
				$block = $blocks[$blockId];

				if(!isset($array["params"])) $array["params"] = array();

				SOY2::cast($block,(object)$array);
				$obj->setBlock($blockId,$block);
			}

			$page->setPageObject($obj);

			$logic = $this->getPageLogic();
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.ComplexPage." . $this->id."?updated&blockId=" . $blockId);
			exit;
		}

		exit;

	}

	private $id;
	private $page;

	function __construct($args){

		$this->id = $args[0];

		$logic = $this->getPageLogic();
		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

		try{
			$page = $dao->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Site.Pages");
			exit;
		}

		$this->page = $page;

		parent::__construct();

		$this->addForm("update_form");
		$this->addForm("add_form");
		$this->addForm("remove_form");

		self::buildForm();
	}

	private function buildForm(){

		$obj = $this->page->getPageObject();
		$page = $this->page;

		$this->addLabel("page_name", array(
			"text" => $page->getName()
		));

		//ブロック情報の取得
		$blocks = $obj->getBlocks();

		if(count($blocks) < 1){
			DisplayPlugin::hide("has_blocks");
		}

		$selectedBlock = null;
		if(isset($_GET["blockId"])){
			$selectedBlock = $_GET["blockId"];
		}else{
			if(count($blocks)){
				$keys = array_keys($blocks);
				$selectedBlock = $keys[0];
			}
		}

		$this->addSelect("block_list", array(
			"name" => "",
			"options" => $blocks,
			"property" => "blockId",
			"selected" => $selectedBlock,
			"onchange" => "javascript:select_detail(this);"
		));

		$this->createAdd("block_detail_list","_common.Site.Pages.Extra.BlockDetailListComponent", array(
			"selected" => $selectedBlock,
			"list" => $blocks
		));
	}

	function getPageLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.site.page.PageLogic");
		return $logic;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品ブロックの設定", array("Site.Pages" => "ページ管理", "Site.Pages.Detail." . $this->page->getId() => "ページ設定"));
	}
}
