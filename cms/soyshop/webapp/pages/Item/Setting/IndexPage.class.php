<?php

class IndexPage extends WebPage{
	
	private $page;
	
	function doPost(){
		
		if(soy2_check_token() && count($_POST["items"])){
			
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			foreach($_POST["items"] as $itemId){
				try{
					$item = $itemDao->getById($itemId);
				}catch(Exception $e){
					continue;
				}
				
				if(isset($_POST["change"])){
					$item->setCategory($_POST["category"]);
				}elseif(isset($_POST["remove"])){
					$item->setCategory(null);
				}
				
				try{
					$itemDao->update($item);
				}catch(Exception $e){
					//
				}
			}
			
			SOY2PageController::jump("Item.Setting?success");
		}
		
		SOY2PageController::jump("Item.Setting?failed");
	}
	
	function IndexPage($args){
		MessageManager::addMessagePath("admin");
		
		$session = SOY2ActionSession::getUserSession();
		$appLimit = $session->getAttribute("app_shop_auth_limit");
		
		WebPage::WebPage();
		
		DisplayPlugin::toggle("app_limit_function", $appLimit);
		
		$this->addForm("form");
		
		$categories = self::getCategories();
		$catList = self::buildCategoryList($categories);
		$selectCat = self::getParameter("category");
		
		$this->addSelect("category_select", array(
			"options" => $catList,
			"selected" => $selectCat,
			"onchange" => "redirectAfterSelect(this);"
		));
						
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => self::getItems($selectCat, $catList),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $categories,
			"config" => $config,
			"appLimit" => $appLimit
		));
		
		$this->addSelect("category_change_select", array(
			"name" => "category",
			"options" => $catList,
		));
	}
	
	private function getItems($selectCat, $catList){
		
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$sql = "SELECT * FROM soyshop_item ".
				"WHERE is_disabled != " . SOYShop_Item::IS_DISABLED . " ";
		
		$binds = array();
		
		//カテゴリがnullの場合は、	
		if(is_null($selectCat) || (int)$selectCat < 0){
			$catIds = array_keys($catList);
			$sql .= "AND (item_category NOT IN (" . implode(",", $catIds) . ") OR item_category IS NULL) ";
		}else{
			$sql .= "AND item_category = :cat ";
			$binds[":cat"] = (int)$selectCat;
		}
		
		try{
			$res = $itemDao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$items = array();
		foreach($res as $values){
			$items[] = $itemDao->getObject($values);
		}
		
		return $items;
	}
	
	private function getCategories(){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function buildCategoryList($categories){
		$list = array();
		foreach($categories as $category){
			$list[(string)$category->getId()] = $category->getName();
		}
		return $list;
	}
	
	private function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			$this->setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Item.Setting.Search:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Item.Setting.Search:" . $key, $value);
	}
	
	function buildSortLink(SearchItemLogic $logic,$sort){

		$link = SOY2PageController::createLink("Item.Setting");

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