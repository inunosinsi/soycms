<?php
class CustomSearchFieldSearch extends SOYShopSearchModule{
	
	private $searchLogic;
	
	/**
	 * title text
	 */
	function getTitle(){
		return "Custom Search Field";
	}
	
	/**
	 * @return html
	 */
	function getForm(){}
		
	/**
	 * @return array<soyshop_item>
	 */
	function getItems($current, $limit){ 
		self::prepare();
		return $this->searchLogic->search($current, (int)$limit);
	}
	
	/**
	 * @return number
	 */
	function getTotal(){
		self::prepare();	
		return $this->searchLogic->getTotal();
	}
	
	private function prepare(){
		if(!$this->searchLogic) $this->searchLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.SearchLogic");
	}
}
SOYShopPlugin::extension("soyshop.search", "custom_search_field", "CustomSearchFieldSearch");