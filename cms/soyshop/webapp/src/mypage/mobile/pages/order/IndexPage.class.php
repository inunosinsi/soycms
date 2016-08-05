<?php
class IndexPage extends MobileMyPagePageBase{

	function __construct($args){
		WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");

		$user = $this->getUser();

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		$this->addLink("return_link", array(
			"link" => soyshop_get_mypage_url() . "/top"
		));

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
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
		$start = $offset;
		$end = $start + count($orders);
		if($end > 0 && $start == 0)$start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPublishPageUrl("order");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$pager->buildPager($this);

		$this->createAdd("order_list","OrderList", array(
			"list" => $orders
		));


	}
}



class OrderList extends HTMLList{

	function populateItem($entity){

		//注文時刻
		$this->createAdd("order_date","HTMLLabel", array(
			"text" => date("Y年m月d日 H時i分s秒",$entity->getOrderDate())
		));

		//注文番号
		$this->createAdd("order_number","HTMLLabel", array(
			"text" => $entity->getTrackingNumber()
		));

		//合計金額
		$this->createAdd("order_price","HTMLLabel", array(
			"text" => $entity->getPrice()
		));
		//詳細リンク
		$this->createAdd("order_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/order/detail/" . $entity->getId()
		));

		return $entity->isOrderDisplay();

	}

}
?>