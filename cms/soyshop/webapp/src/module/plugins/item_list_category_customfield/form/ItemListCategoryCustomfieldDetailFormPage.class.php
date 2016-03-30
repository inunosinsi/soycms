<?php

class ItemListCategoryCustomfieldDetailFormPage extends WebPage{
	
	private $configObj;
	private $itemId;
	private $moduleId;
	private $configs;
	
	private $itemAttributeDao;
	
	function ItemListCategoryCustomfieldDetailFormPage(){
		SOY2::import("domain.shop.SOYShop_CategoryAttribute");
		$this->configs = SOYShop_CategoryAttributeConfig::load();
		$this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addSelect("category_customfield_list", array(
			"name" => "ItemListCategoryCustomfield",
			"options" => $this->getCategoryFieldList(),
			"selected" => $this->getItemAttributeValue()
		));
	}
	
	function getCategoryFieldList(){
		$list = array();
		
		foreach($this->configs as $config){
			$list[$config->getFieldId()] = $config->getLabel();
		}
		
		return $list;
	}
	
	function getItemAttributeValue(){
		try{
			$obj = $this->itemAttributeDao->get($this->itemId, $this->moduleId);
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}
		
		return $obj->getValue();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	function setModuleId($moduleId){
		$this->moduleId = $moduleId;
	}
}
?>