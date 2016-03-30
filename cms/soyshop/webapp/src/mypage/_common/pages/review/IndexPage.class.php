<?php
SOY2::import("module.plugins.item_review.common.ItemReviewCommon");
SOY2::imports("module.plugins.item_review.domain.*");
SOY2::imports("module.plugins.item_review.logic.*");
class IndexPage extends MainMyPagePageBase{

	private $config;
	
	//表示件数
	private $limit = 15;

    function IndexPage($args) {
    	
    	if(!class_exists("SOYShopPluginUtil")) SOY2::import("util.SOYShopPluginUtil");
    	
    	//レビュープラグインがアクティブでない場合はマイページトップへ飛ばす
		if(!SOYShopPluginUtil::checkIsActive("item_review")){
			$this->jumpToTop();
		}
    	
    	$mypage = MyPageLogic::getMyPage();
    	
    	//ログインしていない場合はログイン画面に飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}

		WebPage::WebPage();

		$user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));
		
		$this->addModel("deleted", array(
			"visible" => (isset($_GET["deleted"]))
		));
		
		$this->addModel("failed", array(
			"visible" => (isset($_GET["failed"]))
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
		
		$this->addModel("no_review", array(
			"visible" => (count($reviews) === 0)
		));
		
		$this->addModel("is_review", array(
			"visible" => (count($reviews) > 0)
		));
		
		$this->createAdd("reviews_list", "_common.review.MypageReviewsListComponent", array(
			"list" => $reviews,
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
			"config" => $this->getConfig()
		));
		
		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
    }
    
    function getConfig(){
		return ItemReviewCommon::getConfig();
	}
}
?>