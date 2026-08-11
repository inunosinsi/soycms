<?php
SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
SOY2::imports("module.plugins.item_review.domain.*");
SOY2::imports("module.plugins.item_review.logic.*");
class IndexPage extends MainMyPagePageBase{

	private $config;

	//表示件数
	private $limit = 15;

    function __construct($args) {
		$this->checkIsLoggedIn(); //ログインチェック

    	//レビュープラグインがアクティブでない場合はマイページトップへ飛ばす
		if(!SOYShopPluginUtil::checkIsActive("item_review")) $this->jumpToTop();

		parent::__construct();

		$user = $this->getUser();

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		DisplayPlugin::toggle("deleted", isset($_GET["deleted"]));
		$this->addModel("deleted", array(
			"visible" => (isset($_GET["deleted"]))
		));

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		$this->addModel("failed", array(
			"visible" => (isset($_GET["failed"]))
		));

		/*引数など取得*/
		//表示件数
		$limit = $this->limit;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		$page = max(1, $page);
		$offset = ($page - 1) * $limit;

		$searchLogic = SOY2Logic::createInstance("logic.review.SearchReviewLogic");

		//検索条件の投入と検索実行
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder("create_date_desc");
		$searchLogic->setSearchConditionForMyPage($user->getId());
		$total = $searchLogic->getTotalCount();
		$reviews = $searchLogic->getReviews();

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($reviews);

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPublishPageUrl("review");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);

		DisplayPlugin::toggle("no_review", !count($reviews));
		$this->addModel("no_review", array(
			"visible" => (count($reviews) === 0)
		));

		DisplayPlugin::toggle("is_review", count($reviews));
		$this->addModel("is_review", array(
			"visible" => (count($reviews) > 0)
		));

		$this->createAdd("reviews_list", "_common.review.MypageReviewsListComponent", array(
			"list" => $reviews,
			"config" => ItemReviewUtil::getConfig()
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
    }
}
