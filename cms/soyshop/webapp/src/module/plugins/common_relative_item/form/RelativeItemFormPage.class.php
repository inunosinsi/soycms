<?php

class RelativeItemFormPage extends WebPage{
	
	private $item;
	private $configObj;
	
	private $itemDao;
	
	function __construct(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		try{
			$attr = $dao->get($this->item->getId(),"_relative_items");
			$codes = soy2_unserialize($attr->getValue());
			if(!is_array($codes)) $codes = array();
		}catch(Exception $e){
			$codes = array();
		}
		
		$this->createAdd("relative_item_list", "RelativeItemList", array(
			"list" => array_unique($codes),
			"itemDao" => $this->itemDao
		));
				
		$this->addSelect("relative_item_select" , array(
			"options" => $this->buildRelativeItemSelect($codes)
		));
	}
	
	function buildRelativeItemSelect($codes){
		$list = $this->getAllItemList();
		$options = array();
		foreach($list as $obj){
			if(is_numeric($obj->getType())) continue;
			if(!in_array($obj->getCode(), $codes)){
				$options[$obj->getCode()] = $obj->getCode() . " : " . $obj->getName();
			}
		}
		return $options;
	}
	
	function getAllItemList(){
		try{
			$items = $this->itemDao->get();
		}catch(Exception $e){
			$items = array();
		}
		
		return $items;
	}
	
	function setItem($item){
		$this->item = $item;
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}

class RelativeItemList extends HTMLList{
	
	private $itemDao;
	
	protected function populateItem($entity, $key){
		
		$this->addInput("item_code_input", array(
			"name" => "relative_items[]",
			"value" => (isset($entity)) ? $entity : "",
			"id" => "relative_items_" . $key
		));
		
		$this->addModel("label_for", array(
			"attr:for" => "relative_items_" . $key
		));
		
		$this->addLabel("item_name", array(
			"text" => (isset($entity) && strlen($entity)) ? $this->getItemName($entity) : ""
		));
	}
	
	function getItemName($code){

		try{
			$item = $this->itemDao->getByCode($code);
			return $item->getName();
		}catch(Exception $e){
			return "該当の商品が見付かりません";
		}
	}
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>