<?php
class CustomSearchFieldChildListSearch extends SOYShopSearchModule{
	
	private $searchLogic;
	
	/**
	 * title text
	 */
	function getTitle(){
		return "Custom Search Field Child List";
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
		return $this->searchLogic->search($this->getPage(), $current, (int)$limit);
	}
	
	/**
	 * @return number
	 */
	function getTotal(){
		self::prepare();	
		return $this->searchLogic->getTotal();
	}
	
	private function prepare(){
		if(!$this->searchLogic) $this->searchLogic = SOY2Logic::createInstance("module.plugins.custom_search_field_child_list.logic.ChildItemLogic");
	}
}
SOYShopPlugin::extension("soyshop.search", "custom_search_field_child_list", "CustomSearchFieldChildListSearch");