<?php
SOY2::imports("module.plugins.common_point_base.logic.*");
SOY2::imports("module.plugins.common_point_base.domain.*");
class IndexPage extends MainMyPagePageBase{

	//表示件数
	private $limit = 15;

	function __construct($args) {
		
		//ポイント制導入プラグインがアクティブでない場合はトップページに飛ばす
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("common_point_base")){
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
		
		$timeLimit = self::getUsedPointPeriod($user->getId());
		
		//ポイントの期限が切れているか調べてからポイントを表示
		$this->addLabel("point", array(
			"text" => (!is_null($timeLimit) && $timeLimit < time()) ? "期限切れ" : (int)$user->getPoint() . " pt"
		));
		
		//期限
		DisplayPlugin::toggle("is_time_limit", isset($timeLimit));
		$this->addLabel("time_limit", array(
			"text" => date("Y-m-d H:i:s", $timeLimit)
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
		
		$this->createAdd("point_history_list", "PointHistoryList", array(
			"list" => $histories
		));
		
		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}
	
	private function getUsedPointPeriod($userId){
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		$timeLimit = $logic->getPointByUserId($userId)->getTimeLimit();
		return (isset($timeLimit)) ? (int)$timeLimit : null;
	}
}

class PointHistoryList extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLabel("create_date", array(
			"text" => date("Y/m/d H:i:s", $entity->getCreateDate())
		));
		
		$this->addLink("order_link", array(
			"link" => (!is_null($entity->getOrderId())) ? soyshop_get_mypage_url() . "/order/detail/" . $entity->getOrderId() : null
		));
		
		$this->addLabel("content", array(
			"text" => $entity->getContent()
		));
	}
}
?>