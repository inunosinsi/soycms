<?php

class DepositManagerListPage extends WebPage {

	const SEARCH_LIMIT = 15;

	function __construct(){
		SOY2::import("module.plugins.deposit_manager.component.DepositListComponent");
		SOY2::import("module.plugins.deposit_manager.util.DepositManagerUtil");
	}

	function execute(){
		if(isset($_GET["reset"])){
			$cnds = DepositManagerUtil::getParameter("Search");
			SOY2PageController::jump("Extension.deposit_manager");
		}

		parent::__construct();

		self::_buildSearchForm();
		//
		/*引数など取得*/
		//表示件数
		$args = explode("/", $_SERVER["PATH_INFO"]);
		$page = (isset($args[3]) && is_numeric($args[3])) ? (int)$args[3] : DepositManagerUtil::getParameter("Page");
		if(!is_numeric($page)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * self::SEARCH_LIMIT;

		//検索
		$searchLogic = SOY2Logic::createInstance("module.plugins.deposit_manager.logic.SearchDepositLogic");
		$searchLogic->setLimit(self::SEARCH_LIMIT);
		$searchLogic->setOffset($offset);
		$results = $searchLogic->search();

		// //買取一覧を取得する
		$this->createAdd("deposit_list", "DepositListComponent", array(
			"list" => $results
		));

		$total = $searchLogic->getTotal();

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($results);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Extension.deposit_manager");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit(self::SEARCH_LIMIT);

		$pager->buildPager($this);
	}

	private function _buildSearchForm(){

		$cnds = DepositManagerUtil::getParameter("Search");

		$this->addForm("search_form", array("method" => "GET"));

		$types = array("user_name");
		foreach($types as $t){
			$this->addInput("search_" . $t, array(
				"name" => "Search[" . $t . "]",
				"value" => (isset($cnds[$t])) ? $cnds[$t] : ""
			));
		}

		$this->addLabel("search_deposit_status_checkboxes", array(
			"html" => self::_buildStatusCheckBoxes($cnds)
		));

		foreach(array("start", "end") as $t){
			$this->addInput("search_deposit_date_" . $t, array(
				"name" => "Search[deposit_date][" . $t . "]",
				"value" => (isset($cnds["deposit_date"][$t])) ? $cnds["deposit_date"][$t] : ""
			));
		}

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Extension.deposit_manager?reset")
		));
	}

	private function _buildStatusCheckBoxes($cnds){
		SOY2::import("module.plugins.deposit_manager.util.DepositManagerUtil");
		$list = DepositManagerUtil::getSubjectList(true);
		if(!count($list)) return "";

		$html = array();
		foreach($list as $subjectId => $label){
			if(isset($cnds["subject_id"]) && count($cnds["subject_id"]) && is_numeric(array_search($subjectId, $cnds["subject_id"]))){
				$html[] = "<label><input type=\"checkbox\" name=\"Search[subject_id][]\" value=\"" . $subjectId . "\" checked=\"checked\">" . $label . "</label>";
			}else{
				$html[] = "<label><input type=\"checkbox\" name=\"Search[subject_id][]\" value=\"" . $subjectId . "\">" . $label . "</label>";
			}
		}
		return implode("\n", $html);
	}
}
