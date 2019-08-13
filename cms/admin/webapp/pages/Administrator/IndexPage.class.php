<?php
SOY2::import("domain.admin.Administrator");

class IndexPage extends CMSWebPageBase{

	function __construct($args){
		if(!UserInfoUtil::isDefaultUser()){
			$this->jump("Administrator.Detail");
		}

		parent::__construct();

		$this->outputMessage();

		//検索条件のリセット
		if(isset($_GET["reset"])){
			self::setParameter("page", 1);
			//self::setParameter("sort", null);
			self::setParameter("search", array());
			$this->jump("Administrator");
		}

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : self::getParameter("page");
		//if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		//if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);
		self::setParameter("page", $page);

		$offset = ($page - 1) * $limit;

		//表示順
		//$sort = self::getParameter("sort");


		//検索条件
		$search = self::getParameter("search");
		if(is_null($search)) $search = array();
		self::buildSearchForm($search);

		$searchLogic = SOY2Logic::createInstance("logic.admin.Administrator.SearchAdministratorLogic");
		$searchLogic->setSearchCondition($search);
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);

		$entities = $searchLogic->get();
		$total = $searchLogic->total();
		//$entities = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->getLimitedAdministratorList();

		//管理者がいないときはリストを隠して、メッセージを表示
		$this->addModel("main_table", array(
			"visible"=> ($total > 0)
		));
		$this->addLabel("table_title", array(
			"text"=>CMSMessageManager::get("ADMIN_ADMIN_ID"),
			"visible"=>($total > 0)
		));
		$this->createAdd("list", "_common.Administrator.AdministratorListComponent", array(
			"list"	=> $entities,
			"sites"   => SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getSiteList(),
			"visible" => ($total > 0)
		));
		$this->addLabel("no_administrator", array(
			"text"=>CMSMessageManager::get("ADMIN_MESSAGE_NO_USER"),
			"visible" => ($total == 0)
		));

		$this->addLink("addAdministrator", array(
			"link"=>SOY2PageController::createLink("Administrator.Create"),
			"visible"=>UserInfoUtil::isDefaultUser()
		));

		//自分のパスワード変更
		$this->addLink("changepassword", array(
			"link" => SOY2PageController::createLink("Administrator.ChangePassword")
		));

		$this->addLink("reminderconfig", array(
			"link" => SOY2PageController::createLink("Administrator.Mail"),
			"visible" => UserInfoUtil::isDefaultUser(),
		));

		//ページャ
		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($entities);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Administrator");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);
	}

	private function buildSearchForm($search){
		$this->addForm("search_form");

		foreach(array("user_id", "name", "email") as $t){
			$this->addInput("search_" . $t, array(
				"name" => "search[" . $t . "]",
				"value" => (isset($search[$t])) ? $search[$t] : ""
			));
		}
	}

	/**
	 * メッセージ出力
	 */
	function outputMessage(){
		$messages = CMSMessageManager::getMessages();
		$this->addLabel("message", array(
			"text" => implode("\n",$messages),
			"visible" => !empty($messages)
		));
		$this->addModel("has_message", array(
				"visible" => count($messages),
		));
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Admin.Administrator:" . $key);
		}
		return $value;
	}

	private function setParameter($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("Admin.Administrator:" . $key, $value);
	}
}
