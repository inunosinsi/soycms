<?php

class CollectiveItemStockConfigFormPage extends WebPage{
	
	private $configObj;
	private $itemDao;
	
	private $categories = array();
	
	function __construct(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->categories = self::getCategories();
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			
			if(isset($_POST["Stock"]) && count($_POST["Stock"])){
				
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
					
					$item->setStock($stock);
					
					try{
						$this->itemDao->update($item);
					}catch(Exception $e){
						//
					}
				}
				$this->itemDao->commit();
			}
			
			$this->configObj->redirect("updated");
		}
		
	}
	
	function execute(){
		MessageManager::addMessagePath("admin");
		
		WebPage::WebPage();
		
		$this->addForm("form");
				
		SOY2::import("domain.config.SOYShop_ShopConfig");
		SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.ItemListComponent");
		$this->createAdd("item_list", "ItemListComponent", array(
			"list" => self::getItems(),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"categoriesDAO" => SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $this->categories,
			"config" => SOYShop_ShopConfig::load(),
		));
	}
	
	private function getItems(){
		try{
			return $this->itemDao->get();
		}catch(Exception $e){
			return array();
		}
/**
		$searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.SearchLogic");
		$searchLogic->setLimit(50);	//仮
		$searchLogic->setCondition(self::getParameter("search_condition"));
		return $searchLogic->get();
**/
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
	
	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>