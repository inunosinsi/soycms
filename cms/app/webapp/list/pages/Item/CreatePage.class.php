<?php

class CreatePage extends WebPage{
	
	private $categoryDao;
	
	function doPost(){
		
		$item = $_POST["item"];
		
		if(soy2_check_token()){
			if(isset($_POST["item"]) and $this->checkValidate($item["name"])){
				
				if(isset($_FILES["image"]["name"]) and preg_match('/(jpg|jpeg|gif|png)$/',$_FILES["image"]["name"])){
					$uploadLogic = SOY2Logic::createInstance("logic.UploadLogic");
					$file = $uploadLogic->uploadFile($_FILES["image"]["name"],$_FILES["image"]["tmp_name"]);
					$item["image"] = $file;
				}
				
				$item["price"] = $this->convertNumber($item["price"]);
				$item["url"] = $this->checkUrlValidate($item["url"]);
				
				$dao = SOY2DAOFactory::create("SOYList_ItemDAO");

				$item = SOY2::cast("SOYList_Item",$item);			
				
				try{
					$id = $dao->insert($item);
				}catch(Exception $e){
					var_dump($e);
				}
				CMSApplication::jump("Item.Detail.".$id."?updated");
			}
		}
		
	}
	
    function __construct() {
    	$this->categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	
    	WebPage::WebPage();
    	
    	$this->createAdd("form","HTMLForm",array(
    		"enctype" => "multipart/form-data"
    	));
    	
    	$this->createAdd("name","HTMLInput",array(
    		"name" => "item[name]",
    		"value" => @$_POST["item"]["name"]
    	));
    	
		$this->createAdd("category","HTMLSelect",array(
			"name" => "item[category]",
			"options" => $this->getCategories(),
			"selected" => @$_POST["item"]["category"]
		 ));
		 
		 $this->createAdd("price","HTMLInput",array(
		 	"name" => "item[price]",
		 	"value" => @$_POST["item"]["price"]
		 ));
		 
		 $this->createAdd("standard","HTMLInput",array(
		 	"name" => "item[standard]",
		 	"value" => @$_POST["item"]["standard"]
		 ));
		 
		 $this->createAdd("description","HTMLTextArea",array(
		 	"name" => "item[description]",
		 	"value" => @$_POST["item"]["description"]
		 ));
		 
		 $this->createAdd("url","HTMLInput",array(
		 	"name" => "item[url]",
		 	"value" => @$_POST["item"]["url"]
		 ));
		 
    }
    
    function getCategories(){
    	
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
    
    function checkValidate($str){
    	$flag = false;
    	
    	if(strlen($str)>0){
    		$flag = true;
    	}

    	return $flag;
    }
    
    function convertNumber($int){
    	$int = mb_convert_kana($int,"n");
    	if(!preg_match('/^[0-9]+$/',$int))$int = null;
    	
    	return $int;
    }
        
    function checkUrlValidate($str){
    	if(!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/',$str))$str = null;
    	return $str;
    }
}
?>