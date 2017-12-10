<?php

class SearchPage extends WebPage{

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
					$itemDao->insert($item);
					SOY2PageController::jump("Order.Register.Item.Search");
				}catch(Exceptino $e){
					SOY2PageController::jump("Order.Register.Item.Search?failed");
				}
			}
		}
	}

	function __construct(){

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
	}

	private function buildSearchResult(){
		$cnds = self::getParameter("search_condition");
		if(!is_array($cnds) || is_null($cnds)) $cnds = array();

		//検索結果は5件
		if(count($cnds)){
			$limit = 5;
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
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
		));
	}

	private function buildItemRegisterForm(){

		$cnds = self::getParameter("search_condition");

		$this->addLabel("error", array(
			"text" => (isset($_GET["failed"])) ? "商品登録に失敗しました" : "登録されている商品がありません"
		));

		$this->addForm("register_form");

		foreach(array("name", "code", "price", "stock") as $t){
			$v = (isset($cnds[$t])) ? $cnds[$t] : null;
			if(is_null($v) && $t == "stock") $v = 100;
			$typeProp = ($t == "price" || $t == "stock") ? "number" : "text";
			$this->addInput("register_item_" . $t, array(
				"type" => $typeProp,
				"name" => "Item[" . $t . "]",
				"value" => $v,
				"attr:required" => "required"
			));
		}
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
