<?php

class IndexPage extends WebPage{

    function __construct() {
    	
    	$listDao = SOY2DAOFactory::create("SOYList_ListDAO");
    	$obj = $listDao->get();
   
       	$list = $obj->getConfig();
    	if(!is_null($list)){
    		array_pop($list); //アコーディオンの配列を削除する
    		$update = array_pop($list);
    	}
    	    	
    	WebPage::WebPage();
    	
    	$this->createAdd("is_config","HTMLModel",array(
    		"visible" => (!is_null($list))
    	));
    	$this->createAdd("no_config","HTMLModel",array(
    		"visible" => (is_null($list))
    	));
    	
    	$this->createAdd("update_date","HTMLLabel",array(
    		"text" => date("Y-m-d H:i",$update)
    	));
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => count($list) == 0
    	));
    	
    	$this->createAdd("item_list","ItemList",array(
    		"list" => $this->getItems($list),
    		"category" => $this->getCategories()
    	));
    	
    	$this->createAdd("upload_path","HTMLLabel",array(
    		"text" => SOY_LIST_IMAGE_UPLOAD_DIR
    	));
    }
    
    function getItems($ids){
    	$itemDao = SOY2DAOFactory::create("SOYList_ItemDAO");
    	try{
    		$items = $itemDao->getItemsByIds($ids);
    	}catch(Exception $e){
    		$items = array();
    	}
    	return $items;
    }
    
    function getCategories(){
    	if(!$this->categoryDao)$this->categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	try{
    		$categories = $this->categoryDao->get();
    	}catch(Exception $e){
    		$categories = array();
    	}
    	
    	$array = array();
    	
    	foreach($categories as $category){
    		$array[$category->getId()] = $category->getName();
    	}
    	
    	return $array;
    }
}

class ItemList extends HTMLList{
	
	private $category;
		
	protected function populateItem($item){
		
		$this->createAdd("item_name","HTMLLabel",array(
			"text" => $item->getName()
		));
		
		$this->createAdd("item_name_link","HTMLLink",array(
			"text" => $item->getName(),
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Item.Detail.".$item->getId())
		));
		
		$this->createAdd("item_category","HTMLLabel",array(
			"text" => $this->category[$item->getCategory()]
		));
		
		$this->createAdd("item_price","HTMLLabel",array(
			"text" => number_format($item->getPrice())
		));
		
		$this->createAdd("item_description","HTMLLabel",array(
			"text" => $item->getDescription()
		));
	}
	
	function setCategory($category){
		$this->category = $category;
	}
}
?>