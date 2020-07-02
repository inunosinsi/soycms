<?php

class InquiryListPage extends WebPage {

	private $configObj;
	const SEARCH_LIMIT = 30;

	function __construct(){
		SOY2::import("module.plugins.inquiry_on_mypage.domain.SOYShop_InquiryDAO");
		SOY2::import("module.plugins.inquiry_on_mypage.util.InquiryOnMypageUtil");
	}

	function execute(){
		if(isset($_GET["reset"])){
			$cnds = InquiryOnMypageUtil::getParameter("Search");
			$page = InquiryOnMypageUtil::getParameter("Page");
			$this->configObj->redirect("list");
		}

		parent::__construct();

		self::_buildSearchForm();

		/*引数など取得*/
		//表示件数
		$page = 1;
		$page = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? (int)$_GET["page"] : InquiryOnMypageUtil::getParameter("Page");
		if(!is_numeric($page)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * self::SEARCH_LIMIT;

		//検索
		$searchLogic = SOY2Logic::createInstance("module.plugins.inquiry_on_mypage.logic.SearchInquiryLogic");
		$searchLogic->setLimit(self::SEARCH_LIMIT);
		$searchLogic->setOffset($offset);
		$results = $searchLogic->search();

		SOY2::import("module.plugins.inquiry_on_mypage.component.InquiryListComponent");
		$this->createAdd("inquiry_list", "InquiryListComponent", array(
			"list" => $results
		));

		$total = $searchLogic->getTotal();

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($results);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Config.Detail?plugin=inquiry_on_mypage&list");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit(self::SEARCH_LIMIT);

		$pager->buildPager($this);
	}

	private function _buildSearchForm(){

		$cnds = InquiryOnMypageUtil::getParameter("Search");

		$this->addForm("search_form");

		$types = array("user_name", "mail_address", "tracking_number", "content");
		foreach($types as $t){
			$this->addInput("search_" . $t, array(
				"name" => "Search[" . $t . "]",
				"value" => (isset($cnds[$t])) ? $cnds[$t] : ""
			));
		}

		$this->addCheckBox("search_is_confirm", array(
			"name" => "Search[is_confirm][]",
			"value" => SOYShop_Inquiry::IS_CONFIRM,
			"selected" => (isset($cnds["is_confirm"]) && is_array($cnds["is_confirm"]) && is_numeric(array_search(SOYShop_Inquiry::IS_CONFIRM, $cnds["is_confirm"]))),
			"label" => "確認済み"
		));

		$this->addCheckBox("search_no_confirm", array(
			"name" => "Search[is_confirm][]",
			"value" => SOYShop_Inquiry::NO_CONFIRM,
			"selected" => (isset($cnds["is_confirm"]) && is_array($cnds["is_confirm"]) && is_numeric(array_search(SOYShop_Inquiry::NO_CONFIRM, $cnds["is_confirm"]))),
			"label" => "未確認"
		));

		foreach(array("start", "end") as $t){
			$this->addInput("search_create_date_" . $t, array(
				"name" => "Search[create_date][" . $t . "]",
				"value" => (isset($cnds["create_date"][$t])) ? $cnds["create_date"][$t] : ""
			));
		}

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=inquiry_on_mypage&list&reset")
		));

		//CSS
		// $this->addModel("data_picker_css", array(
		// 	"attr:href" => SOY2PageController::createRelativeLink("./js/tools/soy2_date_picker.css")
		// ));

		//JS
		$this->addModel("data_picker_ja_js", array(
			"src" => SOY2PageController::createRelativeLink("./js/tools/datepicker-ja.js")
		));
		$this->addModel("data_picker_js", array(
			"src" => SOY2PageController::createRelativeLink("./js/tools/datepicker.js")
		));
	}

	private function _getInquiries(){
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$inqDao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
		$inqDao->setOrder("create_date DESC");
		try{
			return $inqDao->get();
		}catch(Exception $e){
			return array();
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
