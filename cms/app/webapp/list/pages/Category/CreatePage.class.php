<?php

class CreatePage extends WebPage{
	
	function doPost(){
		
		$category = $_POST["category"];		
		
		if(soy2_check_token()){
			if(isset($_POST["category"]) and $this->checkValidate($category["name"])){
								
				$dao = SOY2DAOFactory::create("SOYList_CategoryDAO");
				
				
				$category = SOY2::cast("SOYList_Category",$category);

				$time = time();				
				$category->setCreateDate($time);
				$category->setUpdateDate($time);
								
				try{
					$id = $dao->insert($category);	
				}catch(Exception $e){
					var_dump($e);
				}
				CMSApplication::jump("Category.Detail.".$id."?updated");
			}
		}
		
	}

    function __construct() {
    	
    	
    	WebPage::__construct();
    	 
    	$this->createAdd("form","HTMLForm");
    	   	
    	$this->createAdd("name","HTMLInput",array(
    		"name" => "category[name]",
    		"value"	 => @$_POST["category"]["name"]
    	));
    	
    	$this->createAdd("memo","HTMLInput",array(
    		"name" => "category[memo]",
    		"value" => @$_POST["category"]["memo"]
    	));
    }
    
    function checkValidate($str){
    	$flag = false;
    	
    	if(strlen($str)>0){
    		$flag = true;
    	}

    	return $flag;
    	
    }
}
?>