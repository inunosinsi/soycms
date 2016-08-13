<?php

class ListPage extends WebPage{

    function __construct($args) {
    	
    	$id = (isset($args[0])) ? $args[0] : null;
    	
    	$categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	try{
    		$category = $categoryDao->getById($id);
    	}catch(Exception $e){
    		$category = new SOYList_Category();
    	}
    	
    	WebPage::__construct();
    	
    	$this->createAdd("category_name","HTMLLabel",array(
    		"text" => htmlspecialchars($category->getName())
    	));
    	
    	$itemDao = SOY2DAOFactory::create("SOYList_ItemDAO");
    	
    	try{
    		$items = $itemDao->getByCategory($category->getid());
    	}catch(Exception $e){
    		$items = array();
    	}
    	
    	$this->createAdd("item_list","_common.ItemListComponent",array(
    		"list" => $items
    	));
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => (count($items) == 0)
    	));
    }
}
?>