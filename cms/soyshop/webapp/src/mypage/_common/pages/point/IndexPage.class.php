<?php
SOY2::imports("module.plugins.common_point_base.logic.*");
SOY2::imports("module.plugins.common_point_base.domain.*");
class IndexPage extends MainMyPagePageBase{

	//表示件数
	private $limit = 15;

	function __construct($args) {
		$this->checkIsLoggedIn(); //ログインチェック

		//ポイント制導入プラグインがアクティブでない場合はトップページに飛ばす
		if(!SOYShopPluginUtil::checkIsActive("common_point_base")) $this->jumpToTop();

		parent::__construct();

		$user = $this->getUser();

		self::displayPointInformation($user);


		/*引数など取得*/
		//表示件数
		$limit = $this->limit;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)){
			$page = $_GET["page"];
		}
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		$searchLogic = SOY2Logic::createInstance("SearchPointHistoryLogic");

		//検索条件の投入と検索実行
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder("create_date_desc");
		$searchLogic->setSearchConditionForMyPage($user->getId());
		$total = $searchLogic->getTotalCount();
		$histories = $searchLogic->getHistories();

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($histories);

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPublishPageUrl("point");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);

		$this->addModel("no_history", array(
			"visible" => (count($histories) === 0)
		));

		$this->addModel("has_history", array(
			"visible" => (count($histories) > 0)
		));

		$this->createAdd("point_history_list", "_common.point.PointHistoryListComponent", array(
			"list" => $histories
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}

	private function displayPointInformation(SOYShop_User $user){

		$this->addLabel("user_name", array(
				"text" => $user->getName()
		));

		$point = $user->getPoint();
		$limit = ($point) ? $user->getPointTimeLimit(): null ;
		$expiredPoint = 0;
		$isExpired = false;

		//有効期限切れ
		if($limit >0 && $limit < time()){
			$expiredPoint = $point;
			$point = 0;
			$isExpired = true;
		}

		$this->addLabel("point", array(
			"text" => $point,
		));

		$this->addLabel("point_limit", array(
			"text" => isset($limit) ? date("Y/m/d H:i:s", $limit) : "-",
		));
		$this->addModel("point_has_limit", array(
			"visible" => $point && !is_null($limit),
		));

		$this->addModel("point_is_expired", array(
			"visible" => $isExpired,
		));
		$this->addLabel("point_expired", array(
			"text" => $expiredPoint,
		));

	}
}
