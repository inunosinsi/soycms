<?php

class IndexPage extends WebPage{
	
	private $categoryDao;
	
	function doPost(){
		
		if(soy2_check_token()){
			$values = $_POST["Category"]["sort"];
			
			foreach($values as $key => $value){
				if((int)$value===0)$value = 100000;
				try{
					$category = $this->categoryDao->getById($key);
				}catch(Exception $e){
					continue;
				}
				
				$category->setSort((int)$value);
				try{
					$this->categoryDao->update($category);
				}catch(Exception $e){
					continue;
				}
			}
			CMSApplication::jump("Category?updated");
		}
		
	}

    function __construct() {
    	
    	$this->categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	try{
    		$categories = $this->categoryDao->get();
    	}catch(Exception $e){
    		$categories = array();
    	}
    	
    	WebPage::__construct();
    	
    	$this->addModel("updated",array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	
    	$this->addForm("form");
    	
    	$this->createAdd("is_category","HTMLModel",array(
    		"visible" => count($categories)
    	));
    	
    	$this->createAdd("category_list","_common.CategoryListComponent",array(
    		"list" => $categories
    	));
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => count($categories) == 0
    	));
    }
}
?>