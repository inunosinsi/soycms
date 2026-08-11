<?php

class SupplierManagerListPage extends WebPage {

	const SEARCH_LIMIT = 15;

	function __construct(){
		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("module.plugins.supplier_manager.component.SupplierListComponent");
		SOY2::import("module.plugins.supplier_manager.util.SupplierManagerUtil");
	}

	function execute(){
		if(isset($_GET["reset"])){
			$cnds = SupplierManagerUtil::getParameter("Search");
			$params = SupplierManagerUtil::getParameter("Param");
			SOY2PageController::jump("Extension.supplier_manager");
		}

		parent::__construct();

		$this->addLink("create_page_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.supplier_manager")
		));

		self::_buildSearchForm();

		/*引数など取得*/
		//表示件数
		$args = explode("/", $_SERVER["PATH_INFO"]);
		$page = (isset($args[3]) && is_numeric($args[3])) ? (int)$args[3] : SupplierManagerUtil::getParameter("Page");
		if(!is_numeric($page)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * self::SEARCH_LIMIT;

		//検索
		$searchLogic = SOY2Logic::createInstance("module.plugins.supplier_manager.logic.SearchSupplierLogic");
		$searchLogic->setLimit(self::SEARCH_LIMIT);
		$searchLogic->setOffset($offset);
		$results = $searchLogic->search();

		$this->createAdd("supplier_list", "SupplierListComponent", array(
			"list" => $searchLogic->search()
		));

		$total = $searchLogic->getTotal();

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($results);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Extension.supplier_manager");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit(self::SEARCH_LIMIT);

		$pager->buildPager($this);
	}

	private function _buildSearchForm(){

		$cnds = SupplierManagerUtil::getParameter("Search");

		$this->addForm("search_form", array("method" => "GET"));

		$types = array("name");
		foreach($types as $t){
			$this->addInput("search_" . $t, array(
				"name" => "Search[" . $t . "]",
				"value" => (isset($cnds[$t])) ? $cnds[$t] : ""
			));
		}

		$this->addSelect("search_area", array(
			"name" => "Search[area]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => (isset($cnds["area"])) ? $cnds["area"] : null
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Extension.supplier_manager?reset")
		));
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.supplier_manager.logic.SupplierLogic");
		return $logic;
	}
}
