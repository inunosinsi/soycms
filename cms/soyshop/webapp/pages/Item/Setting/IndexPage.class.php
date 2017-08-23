<?php

class IndexPage extends WebPage{
	
	private $page;
	private $itemDao;
	
	private $categories = array();
	
	function doPost(){
		
		if(soy2_check_token()){
			if(count($_POST["items"])){
			
			
				foreach($_POST["items"] as $itemId){
					try{
						$item = $this->itemDao->getById($itemId);
					}catch(Exception $e){
						continue;
					}
					
					if(isset($_POST["change"])){
						$item->setCategory($_POST["category"]);
					}elseif(isset($_POST["remove"])){
						$item->setCategory(null);
					}
					
					try{
						$this->itemDao->update($item);
					}catch(Exception $e){
						//
					}
				}
			
				SOY2PageController::jump("Item.Setting?success");
			}
			
			if(isset($_POST["reset"])){
				$this->setParameter("search", null);
				$this->setParameter("SearchForm", null);
				SOY2PageController::jump("Item.Setting");
			}
		}
	}
	
	function __construct($args){
		MessageManager::addMessagePath("admin");
		
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->categories = self::getCategories();
		
		parent::__construct();

		if(isset($_REQUEST["search"])){
			$this->setParameter("search", true);
		}
		
		$this->addForm("search_form");
		$searchItems = self::buildForm();
		
		//リセットしている時もしくはGETの値が何もない時は強制的に検索を止める
		if(isset($_POST["reset"])) $searchItems = null;
		
		/*引数など取得*/
		//表示件数
		$limit = 50;

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.shop.item.SearchItemLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setSearchCondition($searchItems);

		//データ取得
		$items = (count($searchItems)) ? $searchLogic->getItems() : array();

		/*表示*/

		//表示順リンク
//		$this->buildSortLink($searchLogic, $sort);
		
		$appLimit = SOY2ActionSession::getUserSession()->getAttribute("app_shop_auth_limit");
		DisplayPlugin::toggle("app_limit_function", $appLimit);
		
		$this->addForm("form");		
		
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $this->categories,
			"config" => SOYShop_ShopConfig::load(),
			"appLimit" => $appLimit
		));
		
		$this->addSelect("category_change_select", array(
			"name" => "category",
			"options" => self::buildCategoryList(),
		));
	}
	
	private function buildForm(){
		$form = $this->getParameter("SearchForm");
		$form = (is_array($form)) ? $form : array("is_open" => 1, "is_sale" => 0, "type" => array(SOYShop_Item::TYPE_SINGLE, SOYShop_Item::TYPE_GROUP, SOYShop_Item::TYPE_DOWNLOAD));
		
		$this->addInput("item_name", array(
			"name" => "SearchForm[name]",
			"value" => (isset($form["name"])) ? $form["name"] : ""
		));

		$this->addCheckBox("is_open", array(
			"elementId" => "is_open_check",
			"name" => "SearchForm[is_open]",
			"value" => 1,
			"selected" => (isset($form["is_open"])),
			"label" => "公開"
		));

		$this->addCheckBox("is_close", array(
			"elementId" => "is_close_check",
			"name" => "SearchForm[is_close]",
			"value" => 1,
			"selected" => (isset($form["is_close"])),
			"label" => "非公開"
		));

		$this->addInput("item_code", array(
			"name" => "SearchForm[code]",
			"value" => (isset($form["code"])) ? $form["code"] : ""
		));

		//カテゴリ
		$this->addSelect("item_category", array(
			"name" => "SearchForm[category]",
			"options" => self::buildCategoryList(true),
			"selected" => $form["category"]
		));
		
		return $form;
	}
	
	private function getCategories(){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function buildCategoryList($minusMode = false){
		$list = array();
		if($minusMode){
			$list["-1"] = "カテゴリなし";
		}
		foreach($this->categories as $category){
			$list[(string)$category->getId()] = $category->getName();
		}
		
		return $list;
	}
	
	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			$this->setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Item.Setting.Search:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Item.Setting.Search:" . $key, $value);
	}
}
?>