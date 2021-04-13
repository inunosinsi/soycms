<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
SOY2::import("domain.config.SOYShop_ShopConfig");
class IndexPage extends WebPage{

	function doPost(){
		if(!AUTH_OPERATE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(!soy2_check_token()) SOY2PageController::jump("Item");

		if(isset($_POST["do_change_publish"])){
			$publish = $_POST["do_change_publish"];
			$items = $_POST["items"];

			$logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");
			$logic->changeOpen($items, $publish);

			SOY2PageController::jump("Item?updated");
			exit;
		}

		if(isset($_POST["do_remove"])){
			$items = $_POST["items"];

			$logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");
			$logic->delete($items);

			SOY2PageController::jump("Item?deleted");
			exit;
		}
	}

	function __construct($args){
		//ダミー商品をたくさん追加
		if(isset($_GET["create"]) && $_GET["create"] == "dummy"){
			$itemLogic = SOY2Logic::createInstance("logic.shop.item.ItemLogic")->createDummyItems();
			SOY2PageController::jump("Item?dummy");
		}

		MessageManager::addMessagePath("admin");

		parent::__construct();

		//一覧ページを開いた時に何らかの処理をする
		SOYShopPlugin::load("soyshop.item");
		SOYShopPlugin::invoke("soyshop.item", array(
			"mode" => "list"
		));

		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Item.Create"),
			"visible" => AUTH_OPERATE
		));

		if(isset($_GET["reset"])){
			$this->setParameter("page", 1);
			$this->setParameter("sort", null);
		}

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) || array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = $this->getParameter("sort");
		$this->setParameter("page", $page);

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.shop.item.SearchItemLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$searchLogic->setSearchCondition(array());

		//データ取得
		$total = (int)$searchLogic->getTotalCount();
		$items = ($total > 0) ? $searchLogic->getItems() : array();

		/*表示*/

		//表示順リンク
		$this->buildSortLink($searchLogic, $sort);

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($items);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Item");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$pager->buildPager($this);

		//在庫数と注文数を事前に取得しておく
		list($stocks, $orders) = self::_getStocksAndOrders($items);

		//ItemListの準備
		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"itemStocks" => $stocks,
			"orderCounts" => $orders,
			"detailLink" => SOY2PageController::createLink("Item.Detail.")
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Item") . "?reset",
			"visible" => ($sort)
		));

		//操作周り
		$this->addForm("item_form");
	}

	//在庫数と注文数を事前に取得しておく
	private function _getStocksAndOrders($items){
		if(!count($items)) return array(array(), array());

		$itemIds = array();
		foreach($items as $item){
			$itemIds[] = $item->getId();
		}

		if(!count($itemIds)) return array(array(), array());

		$stocks = SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getStockListByItemIds($itemIds);
		$orders = SOY2Logic::createInstance("logic.order.OrderLogic")->getOrderCountListByItemIds($itemIds);

		return array($stocks, $orders);
	}

	function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			$this->setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Item.Search:" . $key);
		}
		return $value;
	}
	function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Item.Search:" . $key, $value);
	}

	function buildSortLink(SearchItemLogic $logic,$sort){

		$link = SOY2PageController::createLink("Item");

		$sorts = $logic->getSorts();

		foreach($sorts as $key => $value){

			$text = (!strpos($key,"_desc")) ? "▲" : "▼";
			$title = (!strpos($key,"_desc")) ? "昇順" : "降順";

			$this->addLink("sort_${key}", array(
				"text" => $text,
				"link" => $link . "?sort=" . $key,
				"title" => $title,
				"class" => ($sort === $key) ? "sorter_selected" : "sorter"
			));
		}
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品管理");
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.ItemFooterMenuPage", array(
				"arguments" => array(null)
			))->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
