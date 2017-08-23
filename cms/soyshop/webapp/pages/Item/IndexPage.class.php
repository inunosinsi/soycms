<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
SOY2::import("domain.config.SOYShop_ShopConfig");
class IndexPage extends WebPage{

	function doPost(){

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

		$session = SOY2ActionSession::getUserSession();
		$appLimit = $session->getAttribute("app_shop_auth_limit");

		parent::__construct();

		//管理制限の権限を取得し、権限がない場合は表示しない
		$this->addModel("app_limit_function", array(
			"visible" => $appLimit
		));

		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Item.Create"),
			"visible" => $appLimit
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

		//データ取得
		$total = $searchLogic->getTotalCount();
		$items = $searchLogic->getItems();

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

		//ItemListの準備
		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		try{
			$categories = $categoryDAO->get();
		}catch(Exception $e){
			$categories = array();
		}

		$itemOrderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$categoriesDAO = SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO");

		$config = SOYShop_ShopConfig::load();
		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"itemOrderDAO" => $itemOrderDAO,
			"categoriesDAO" => $categoriesDAO,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $categories,
			"config" => $config,
			"multi" => $config->getMultiCategory(),
			"appLimit" => $appLimit
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Item") . "?reset",
			"visible" => ($sort)
		));

		//操作周り
		$this->addForm("item_form");
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
}
?>