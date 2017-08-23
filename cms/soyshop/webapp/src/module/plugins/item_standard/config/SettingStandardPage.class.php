<?php

class SettingStandardPage extends WebPage{
	
	private $configObj;
	private $itemDao;
	private $itemId;
	private $parentItem;
	
	function __construct(){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->itemId = (int)$_GET["item_id"];
		$this->parentItem = self::getItem();
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Item"])){
			
			$logic = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic");
			SOYShopPlugin::load("soyshop.item.update");
			
			foreach($_POST["Item"] as $values){
				$child = $logic->getChildItem($this->itemId, $values["key"]);
				$child = $logic->setChildItemName($child, $this->parentItem, $values["key"]);
				
				//在庫数チェックの前に更新前の在庫数を取得しておく
				$oldStock = $child->getStock();

				$child->setPrice((int)$values["price"]);
				$child->setSalePrice((int)$values["salePrice"]);
				$child->setStock((int)$values["stock"]);
				
				
				//新規
				if(is_null($child->getId())){
					$child = $logic->setParentInfo($child, $this->parentItem);
					
					try{
						$this->itemDao->insert($child);
					}catch(Exception $e){
						//
					}
				//更新
				}else{
					try{
						$this->itemDao->update($child);
					}catch(Exception $e){
						//
					}
					
					//更新の場合
					SOYShopPlugin::invoke("soyshop.item.update", array(
						"item" => $child,
						"old" => $oldStock
					));
				}
			}
		}
	}
	
	function execute(){
		parent::__construct();
		
		$this->addLink("return_link", array(
			"link" => SOY2PageController::createLink("Item.Detail.") . $this->itemId
		));
		
		self::buildItemInfo();
		
		self::buildStandardList();
	}
	
	private function buildItemInfo(){
		$item = $this->parentItem;
		
		$this->addLabel("item_name", array(
			"text" => $item->getName()
		));
		
		$this->addLabel("item_code", array(
			"text" => $item->getCode()
		));
		
		$this->addLabel("item_price", array(
			"text" => number_format($item->getPrice())
		));
		
		$this->addLabel("item_sale_price", array(
			"text" => number_format($item->getSalePrice())
		));
	}
	
	private function buildStandardList(){
		$this->addForm("form");
		
		$logic = SOY2Logic::createInstance("module.plugins.item_standard.logic.BuildFormLogic", array("parentId" => $this->itemId));
		$this->addLabel("table", array(
			"html" => $logic->buildStandardListArea()
		));
		
		DisplayPlugin::toggle("is_first", $logic->getIsFirst());
	}
	
	private function getItem(){
		try{
			return $this->itemDao->getById($this->itemId);
		}catch(Exception $e){
			return SOYShop_Item();
		}
	}
		
	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>