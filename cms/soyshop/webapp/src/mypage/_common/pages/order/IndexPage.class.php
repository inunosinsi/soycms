<?php 
SOYShopPlugin::load("soyshop.item.option");
class IndexPage extends MainMyPagePageBase{

	//表示件数
	private $limit = 15;

	function __construct($args){
		
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

		$searchLogic = SOY2Logic::createInstance("logic.order.SearchOrderLogic");

		//検索条件の投入と検索実行
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder("order_date_desc");
		$searchLogic->setSearchConditionForMyPage($user->getId());
		$total = $searchLogic->getTotalCount();
		$orders = $searchLogic->getOrders();
		
		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($orders);

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPublishPageUrl("order");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);

		$this->createAdd("order_list", "_common.order.OrderListComponent", array(
			"list" => $orders,
		));
		
		$this->addModel("has_order", array(
			"visible" => (boolean)$total
		));
		$this->addModel("no_order", array(
			"visible" => !( (boolean)$total )
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}
}
?>