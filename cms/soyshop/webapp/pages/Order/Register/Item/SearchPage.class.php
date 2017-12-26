<?php

class SearchPage extends WebPage{

	private $item;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Reset"])){
				self::setParameter("search_condition", null);
				SOY2PageController::jump("Order.Register.Item.Search");
			}

			//商品を登録する
			if(isset($_POST["Register"])){
				$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				$item = SOY2::cast("SOYShop_Item", $_POST["Item"]);

				//他の諸々の設定
				$item->setIsOpen(SOYShop_Item::IS_OPEN);

				try{
					$id = $itemDao->insert($item);
					self::setParameter("search_condition", array("name" => $item->getName(), "code" => $item->getCode(), "category" => $item->getCategory())); //条件を入れる
					SOY2PageController::jump("Order.Register.Item.Search");
				}catch(Exception $e){
					$this->item = $item;
				}
			}
		}
	}

	function __construct(){

		SOY2::import("domain.shop.SOYShop_Item");
		parent::__construct();

		self::buildSearchForm();

		//検索結果を表示
		self::buildSearchResult();

		//簡易的な商品登録用フォーム
		self::buildItemRegisterForm();
	}

	private function buildSearchForm(){
		$cnds = self::getParameter("search_condition");

		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "search_condition[name]",
			"value" => (isset($cnds["name"])) ? $cnds["name"] : null
		));

		$this->addInput("code", array(
			"name" => "search_condition[code]",
			"value" => (isset($cnds["code"])) ? $cnds["code"] : null
		));

		$this->addSelect("category", array(
			"name" => "search_condition[category]",
			"options" => self::getCategoryList(),
			"selected" => (isset($cnds["category"])) ? $cnds["category"] : null
		));
	}

	private function buildSearchResult(){
		$cnds = self::getParameter("search_condition");
		if(!is_array($cnds) || is_null($cnds)) $cnds = array();

		//検索結果は5件
		if(count($cnds)){
			$limit = 15;
			$searchLogic = SOY2Logic::createInstance("logic.shop.item.SearchItemLogic");
			$searchLogic->setLimit($limit);
			$searchLogic->setSearchCondition($cnds);
			$items = $searchLogic->getItems();
			$cnt = (count($items));
			$doSearch = true;	//検索を行ったか？
		}else{
			$items = array();
			$cnt = 0;
			$doSearch = false;
		}

		//検索結果がある場合
		DisplayPlugin::toggle("search_result", $cnt > 0);

		//検索を行っｔ上で検索結果がない場合　buildItemRegisterFormメソッドの表示用
		DisplayPlugin::toggle("search_no_result", ($doSearch && $cnt === 0));

		//商品一覧
		$this->createAdd("item_list", "_common.Order.ItemListComponent", array(
			"list" => $items,
			"categories" => self::getCategoryList(),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
		));
	}

	private function buildItemRegisterForm(){

		$cnds = self::getParameter("search_condition");

		$this->addLabel("error", array(
			"text" => (isset($this->item)) ? "商品登録に失敗しました" : "登録されている商品がありません"
		));
		DisplayPlugin::toggle("error_code", (isset($this->item)));

		if(isset($this->item)){
			$cnds = array(
				"name" => $this->item->getName(),
				"code" => $this->item->getCode(),
				"price" => $this->item->getPrice(),
				"stock" => $this->item->getStock(),
				"unit" => $this->item->getUnit(),
				"category" => $this->item->getCategory(),
				"list_price" => $this->item->getAttribute("list_price")
			);
		}

		$this->addForm("register_form");

		foreach(array("name", "code", "price", "stock", "unit") as $t){
			$v = (isset($cnds[$t])) ? $cnds[$t] : null;
			if($t == "unit" && is_null($v)) $v = SOYShop_Item::UNIT;
			if(is_null($v) && $t == "stock") $v = 100;
			$typeProp = ($t == "price" || $t == "stock") ? "number" : "text";
			$this->addInput("register_item_" . $t, array(
				"type" => $typeProp,
				"name" => "Item[" . $t . "]",
				"value" => $v,
				"attr:required" => "required"
			));
		}

		$this->addSelect("register_item_category", array(
			"name" => "Item[category]",
			"options" => self::getCategoryList(),
			"selected" => (isset($cnds["category"])) ? $cnds["category"] : false
		));

		//定価
		$this->addInput("register_item_list_price", array(
			"type" => "text",
			"name" => "Item[config][list_price]",
			"value" => (isset($cnds["list_price"])) ? $cnds["list_price"] : 0,
		));
	}

	private function getCategoryList(){
		static $list;
		if(is_null($list)){
			$list = array();
			try{
				$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
			}catch(Exception $e){
				return $list;
			}
			if(!count($categories)) return $list;

			foreach($categories as $category){
				$list[$category->getId()] = $category->getName();
			}
		}

		return $list;
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Order.Register.Item.Search:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Order.Register.Item.Search:" . $key, $value);
	}
}
