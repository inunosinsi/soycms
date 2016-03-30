<?php

class IndexPage extends MainMyPagePageBase{
	
	//表示件数
	private $limit = 15;

	function IndexPage($args){
		
		$mypage = MyPageLogic::getMyPage();
		
		//ログインチェック
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}
		
		WebPage::WebPage();
		
		$user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));
		
		/*引数など取得*/
		//表示件数
		$limit = $this->limit;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)){
			$page = $_GET["page"];
		}
		$page = max(1, $page);
		
		$offset = ($page - 1) * $limit;

		$searchLogic = SOY2Logic::createInstance("logic.mail.SearchMailLogLogic");

		//検索条件の投入と検索実行
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder("send_date_desc");
		$searchLogic->setSearchConditionForMyPage($user->getId());
		$total = $searchLogic->getTotalCount();
		$logs = $searchLogic->getLogs();
		
		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($logs);

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPublishPageUrl("mail");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);
		
		$this->addModel("has_log", array(
			"visible" => (boolean)$total
		));
		$this->addModel("no_log", array(
			"visible" => !( (boolean)$total )
		));
		
		$this->createAdd("mail_log_list", "_common.mail.MailLogListComponent", array(
			"list" => $logs
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}
}
?>