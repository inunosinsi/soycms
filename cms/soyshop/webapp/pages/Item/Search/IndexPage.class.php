<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	function IndexPage($args){
		MessageManager::addMessagePath("admin");

		WebPage::WebPage();

		if(isset($_GET["reset"])){
			$this->setParameter("page", 1);
			$this->setParameter("sort", null);
			$this->setParameter("search", null);
			$this->setParameter("SearchForm", null);
		}

		if(isset($_REQUEST["search"])){
			$this->setParameter("search", true);
		}
		
		$this->addForm("search_form");
		$searchItems = $this->buildForm();
		
		//リセットしている時もしくはGETの値が何もない時は強制的に検索を止める
		if(isset($_GET["reset"]) || count($_GET) === 0) $searchItems = null;
		
		/*引数など取得*/
		//表示件数
		$limit = 15;
		//ページはargsのみ
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if($page < 1) $page = 1;
		$offset = ($page - 1) * $limit;

		//表示順
		$sort = $this->getParameter("sort");
		$this->setParameter("page", $page);

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.shop.item.SearchItemLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$searchLogic->setSearchCondition($searchItems);

		//データ取得
		$total = $searchLogic->getTotalCount();
		$items = (count($searchItems)) ? $searchLogic->getItems() : array();

		/*表示*/

		//表示順リンク
		$this->buildSortLink($searchLogic, $sort);

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($items);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Item.Search");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		$pager->setQuery("search");
		$pager->buildPager($this);

		//ItemListの準備
		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
    	$categories = $categoryDAO->get();

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		$this->createAdd("item_list", "_common.Item.SearchItemListComponent", array(
			"list" => $items,
			"orderDAO" => $orderDAO,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $categories
		));

		$this->addModel("search_result", array(
			"visible" => (count($items) > 0)
		));

		$this->addModel("no_result", array(
			"visible" => ($this->getParameter("search") && count($items) == 0)
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Item.Search") . "?reset",
		));

		//操作周り
		$this->addForm("item_form");
	}

	function doPost(){

		if(!soy2_check_token()) SOY2PageController::jump("Item.Search");

		if(isset($_POST["do_change_publish"])){
			$publish = $_POST["do_change_publish"];
			$items = $_POST["items"];

			$logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");
			$logic->changeOpen($items, $publish);

			SOY2PageController::jump("Item.Search?updated");
			exit;
		}

		if(isset($_POST["do_remove"])){
			$publish = $_POST["do_change_publish"];
			$items = $_POST["items"];

			$logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");
			$logic->delete($items);

			SOY2PageController::jump("Item.Search?deleted");
			exit;
		}
	}

	function buildForm(){
		$form = $this->getParameter("SearchForm");
		$form = (is_array($form)) ? $form : array("is_open" => 1, "is_sale" => 0);

		$this->addInput("item_name", array(
			"name" => "SearchForm[name]",
			"value" => (isset($form["name"])) ? $form["name"] : ""
		));

		$this->addCheckBox("is_open", array(
			"elementId" => "is_open_check",
			"name" => "SearchForm[is_open]",
			"value" => 1,
			"selected" => (isset($form["is_open"])),
		));

		$this->addCheckBox("is_close", array(
			"elementId" => "is_close_check",
			"name" => "SearchForm[is_close]",
			"value" => 1,
			"selected" => (isset($form["is_close"])),
		));

		$this->addCheckBox("is_sale", array(
			"elementId" => "is_sale_check",
			"name" => "SearchForm[is_sale]",
			"value" => 1,
			"selected" => (isset($form["is_sale"])),
		));

		$this->addInput("item_code", array(
			"name" => "SearchForm[code]",
			"value" => (isset($form["code"])) ? $form["code"] : ""
		));
		
		$this->addCheckBox("is_child", array(
			"name" => "SearchForm[is_child]",
			"value" => 1,
			"label" => "小商品も表示する",
			"selected" => (isset($form["is_child"]))
		));

		//カテゴリ
		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
    	$categories = $categoryDAO->get();

		$selected_categories = (strlen(@$form["categories"]) > 0) ? explode(" ", trim(@$form["categories"])) : array();

		$this->createAdd("category_tree","_base.MyTreeComponent", array(
			"list" => $categories,
			"root" => (count($selected_categories) > 0) ? "<b>カテゴリ(<span id=\"category_count\">".count($selected_categories)."</span>)</b>" : "カテゴリ",
			"expand" => (count($selected_categories) > 0),
			"selected" => $selected_categories
		));

		$this->addInput("item_categories", array(
			"attr:id" => "item_categories",
			"name" => "SearchForm[categories]",
			"value" => (isset($form["categories"])) ? $form["categories"] : ""
		));

		return $form;
	}

	function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			$this->setParameter($key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Item.Search:" . $key);
		}
		return $value;
	}
	function setParameter($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("Item.Search:" . $key, $value);
	}

	function buildSortLink($logic, $sort){

		$link = SOY2PageController::createLink("Item.Search") . "?search&";

		$sorts = $logic->getSorts();

		foreach($sorts as $key => $value){

			$text = (!strpos($key, "_desc")) ? "▲" : "▼";
			$title = (!strpos($key, "_desc")) ? "昇順" : "降順";

			$this->addLink("sort_${key}", array(
				"text" => $text,
				"link" => $link . "sort=" . $key,
				"title" => $title,
				"class" => ($sort === $key) ? "sorter_selected" : "sorter"
			));
		}
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.pack.js",
		);
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.css",
			$root . "tree.css",
		);
	}
}
?>