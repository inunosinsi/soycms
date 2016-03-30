<?php

class ItemListCategoryCustomfieldSelectFormPage extends WebPage{
	
	private $configObj;
	private $moduleId;
	private $pageId;
	
	function ItemListCategoryCustomfieldSelectFormPage(){
		SOY2::imports("module.plugins.item_list_category_customfield.util.*");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$config = ItemListCategoryCustomfieldUtil::getPageConfig($this->moduleId, $this->pageId);
		
		$this->addSelect("field_list", array(
			"name" => "Other[fieldId]",
			"options" => $this->getFieldList(),
			"selected" => $config["fieldId"]
		));
		
		$this->addCheckBox("useParameter_value", array(
			"name" => "Other[useParameter]",
			"value" => 0,
			"selected" => (is_null($config["useParameter"]) || $config["useParameter"] == 0)
		));
		
		$this->addInput("field_value", array(
			"name" => "Other[fieldValue]",
			"value" => $config["fieldValue"]
		));
		
		$this->addCheckBox("useParameter_arg", array(
			"name" => "Other[useParameter]",
			"value" => 1,
			"label" => "引数と一致する商品一覧",
			"selected" => ($config["useParameter"] == 1)
		));
	}
	
	function getFieldList(){
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$configs = SOYShop_CategoryAttributeConfig::load();
		
		$list = array();
		
		foreach($configs as $config){
			$list[$config->getFieldId()] = $config->getLabel();
		}
		
		return $list;
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
	
	function setModuleId($moduleId){
		$this->moduleId = $moduleId;
	}
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}
?>