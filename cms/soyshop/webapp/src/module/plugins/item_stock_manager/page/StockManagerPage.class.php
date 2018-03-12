<?php

class StockManagerPage extends WebPage{

	private $configObj;
	private $itemDao;

	private $categories = array();

	private $limit = 15;

	function __construct(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->categories = self::getCategories();
		if(isset($_POST["search_number"])) $this->limit = (int)$_POST["search_number"];
	}

	function doPost(){

		if(soy2_check_token()){

			// CSVの出力
			if(isset($_POST["CSV"])){
				$logic = SOY2Logic::createInstance("module.plugins.item_stock_manager.logic.CSVLogic");
				$logic->setLimit($this->limit);
				$logic->setParams(self::getParameter("search_condition"));

				$labels = $logic->getLabels();
				$lines = $logic->getLines();
				$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "Shift-JIS";

				if(count($lines) == 0) return;

				set_time_limit(0);

				header("Cache-Control: public");
				header("Pragma: public");
				header("Content-Disposition: attachment; filename=item_stock_manager_" .date("YmdHis", time()) . ".csv");
				header("Content-Type: text/csv; charset=" . htmlspecialchars($charset).";");

				ob_start();
				echo implode(",", $labels);
				echo "\n";
				echo implode("\n", $lines);
				$csv = ob_get_contents();
				ob_end_clean();

				echo mb_convert_encoding($csv, $charset, "UTF-8");
				exit;
			}

			if(isset($_POST["Stock"]) && count($_POST["Stock"])){

				SOYShopPlugin::load("soyshop.item.update");

				$this->itemDao->begin();
				foreach($_POST["Stock"] as $itemId => $stock){
					//念の為
					if(!is_numeric($stock)) continue;

					//在庫に変更があるか調べる
					try{
						$item = $this->itemDao->getById($itemId);
					}catch(Exception $e){
						continue;
					}

					//変更がない場合は次へ
					if((int)$item->getStock() === (int)$stock) continue;

					$oldStock = (int)$item->getStock();
					$item->setStock($stock);

					try{
						$this->itemDao->update($item);
					}catch(Exception $e){
						//
					}

					//入荷通知プラグインと併用できるように拡張ポイントを追加
					SOYShopPlugin::invoke("soyshop.item.update", array(
						"item" => $item,
						"old" => $oldStock
					));
				}
				$this->itemDao->commit();

				SOY2PageController::jump("Extension.item_stock_manager?updated");
			}
		}
	}

	function execute(){

		//リセット
		if(isset($_POST["reset"])){
			self::setParameter("search_condition", null);
			self::setParameter("page", 1);
			self::setParameter("sort", null);
			SOY2PageController::jump("Extension.item_stock_manager");
		}

		MessageManager::addMessagePath("admin");

		parent::__construct();

		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Item.Create"),
			"visible" => SOY2ActionSession::getUserSession()->getAttribute("app_shop_auth_limit")
		));

		self::buildSearchForm();

		//argsを作る
		$v = trim(substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/")), "/");
		$args[] = (is_numeric($v)) ? (int)$v : null;

		$page = (isset($args[0])) ? (int)$args[0] : self::getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) || array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $this->limit;

		//表示順
		$sort = self::getParameter("sort");
		self::setParameter("page", $page);

		$searchLogic = SOY2Logic::createInstance("module.plugins.item_stock_manager.logic.SearchLogic");
		$searchLogic->setLimit($this->limit);	//仮
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$searchLogic->setCondition(self::getParameter("search_condition"));

		$total = $searchLogic->getTotalCount();
		$items = $searchLogic->get();

		$this->addForm("form");

		SOY2::import("domain.config.SOYShop_ShopConfig");
		SOY2::import("module.plugins.item_stock_manager.component.ItemListComponent");
		$this->createAdd("item_list", "ItemListComponent", array(
			"list" => $items,
			"stockLogic" => SOY2Logic::createInstance("module.plugins.item_stock_manager.logic.StockLogic"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $this->categories
		));

		//表示順リンク
		self::buildSortLink($searchLogic, $sort);

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($items);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Extension.item_stock_manager");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($this->limit);

		$pager->buildPager($this);
	}

	private function buildSearchForm(){

		//POSTのリセット
		if(isset($_POST["search_condition"])){
			foreach($_POST["search_condition"] as $key => $value){
				if(is_array($value)){
					//
				}else{
					if(!strlen($value)){
						unset($_POST["search_condition"][$key]);
					}
				}
			}
		}

		if(isset($_POST["search"]) && !isset($_POST["search_condition"])){
			self::setParameter("search_condition", null);
			$cnd = array();
		}else{
			$cnd = self::getParameter("search_condition");
		}
		//リセットここまで


		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));

		$this->addForm("search_form");

		foreach(array("item_name", "item_code") as $t){
			$this->addInput("search_" . $t, array(
				"name" => "search_condition[" . $t . "]",
				"value" => (isset($cnd[$t])) ? $cnd[$t] : ""
			));
		}

		$opts = array();
		foreach($this->categories as $cat){
			$opts[$cat->getId()] = $cat->getName();
		}
		$this->addSelect("search_item_category", array(
			"name" => "search_condition[item_category]",
			"options" => $opts,
			"selected" => (isset($cnd["item_category"])) ? $cnd["item_category"] : null
		));

		$this->addCheckBox("search_item_is_open", array(
			"name" => "search_condition[item_is_open][]",
			"value" => SOYShop_Item::IS_OPEN,
			"selected" => (isset($cnd["item_is_open"]) && in_array(SOYShop_Item::IS_OPEN, $cnd["item_is_open"])),
			"label" => "公開"
		));

		$this->addCheckBox("search_item_no_open", array(
			"name" => "search_condition[item_is_open][]",
			"value" => SOYShop_Item::NO_OPEN,
			"selected" => (isset($cnd["item_is_open"]) && in_array(SOYShop_Item::NO_OPEN, $cnd["item_is_open"])),
			"label" => "非公開"
		));

		//表示件数
		$this->addInput("search_item_number", array(
			"name" => "search_number",
			"value" => (isset($_POST["search_number"])) ? (int)$_POST["search_number"] : 15,
			"style" => "width: 80px;"
		));

		$this->addCheckBox("search_item_type_parent", array(
			"name" => "search_condition[item_type][parent]",
			"value" => 1,
			"selected" => (!isset($cnd["item_type"]["parent"]) || $cnd["item_type"]["parent"] == 1),
			"label" => "通常商品(親商品)"
		));
		$this->addInput("search_item_type_parent_hidden", array(
			"name" => "search_condition[item_type][parent]",
			"value" => 0,
		));

		$this->addCheckBox("search_item_type_child", array(
			"name" => "search_condition[item_type][child]",
			"value" => 1,
			"selected" => (isset($cnd["item_type"]["child"]) && $cnd["item_type"]["child"] == 1),
			"label" => "子商品"
		));
	}

	private function getCategories(){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Collective.Stock:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Collective.Stock:" . $key, $value);
	}

	function buildSortLink(SearchLogic $logic,$sort){

		$link = SOY2PageController::createLink("Extension.item_stock_manager");
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

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
