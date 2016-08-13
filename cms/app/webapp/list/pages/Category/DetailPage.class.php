<?php

class DetailPage extends WebPage{

	private $id;
	private $categoryDao;

	function doPost(){
		
		$newCategory = $_POST["category"];
		
		if(soy2_check_token()){
			if(isset($_POST["category"]) && $this->checkValidate($newCategory["name"])){
								
				
				$oldCategory = $this->getCategory($this->id);
				$category = SOY2::cast($oldCategory,$newCategory);

				try{
					$this->categoryDao->update($category);
					CMSApplication::jump("Category.Detail." . $category->getId()."?updated");
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}
	}

    function __construct($args) {
    	$this->id = @$args[0];
    	$this->categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	
    	WebPage::__construct();
    	
    	$category = $this->getCategory($this->id);
    	
    	$this->addModel("updated",array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("id","HTMLInput",array(
    		"name" => "category[id]",
    		"value" => $category->getId()
    	));
    	
    	$this->createAdd("name","HTMLInput",array(
    		"name" => "category[name]",
    		"value"	 => $category->getName()
    	));
    	
    	$this->createAdd("memo","HTMLInput",array(
    		"name" => "category[memo]",
    		"value" => $category->getMemo()
    	));
    	
    	$this->createAdd("create_date","HTMLInput",array(
    		"name" => "category[createDate]",
    		"value" => $category->getCreateDate()
    	));
    }
    
    function getCategory($id){
    	try{
    		$category = $this->categoryDao->getById($id);
    	}catch(Exception $e){
    		CMSApplication::jump("Category");
    	}
    	return $category;
    }
    
    function checkValidate($str){
    	$flag = false;
    	
    	if(strlen($str)>0)$flag = true;
    	
    	return $flag;
    	
    }
}
?>