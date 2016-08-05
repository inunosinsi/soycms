<?php

class DetailPage extends WebPage{
	
	private $id;
	private $itemDao;
	private $categoryDao;
	
	function doPost(){
		
		$item = $_POST["item"];
		
		if(soy2_check_token()){
			if(isset($_POST["item"]) and $this->checkValidate($item["name"])){
				
				$uploadLogic = SOY2Logic::createInstance("logic.UploadLogic");
				
				//アップロードした画像を削除する
				if(isset($_POST["delete"]) and $_POST["delete"] == 1){
					$uploadLogic->deleteFile($item["image"]);
					$item["image"] = null;
					
				//画像をアップロードしたり、アップロードした画像を残したり
				}else{
					//画像をアップロードする。
					if(isset($_FILES["image"]["name"]) and preg_match('/(jpg|jpeg|gif|png)$/',$_FILES["image"]["name"])){
						
						$file = $uploadLogic->uploadFile($_FILES["image"]["name"],$_FILES["image"]["tmp_name"]);
						$item["image"] = $file;
					}
				
					//アップロードする画像がない場合。
					if(!isset($_FILES["image"]["name"]) and strlen($_POST["image"])>0)$item["image"] = $_POST["image"];
				}
						
				
				
				$item["price"] = $this->convertNumber($item["price"]);
				$item["url"] = $this->checkUrlValidate($item["url"]);
				
				$oldItem = $this->getItem($this->id);

				$item = SOY2::cast($oldItem,$item);

				try{
					$this->itemDao->update($item);
				}catch(Exception $e){
					
				}
				CMSApplication::jump("Item.Detail.".$item->getId()."?updated");
			}
		}
		
	}
	
    function __construct($args) {
    	
    	$this->id = @$args[0];
    	$this->itemDao = SOY2DAOFactory::create("SOYList_ItemDAO");
    	$this->categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	
    	WebPage::WebPage();
    	
    	$item = $this->getItem($this->id);
    	
    	$this->addModel("updated",array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	
    	$this->createAdd("form","HTMLForm",array(
    		"enctype" => "multipart/form-data"
    	));
    	
    	$this->createAdd("name","HTMLInput",array(
    		"name" => "item[name]",
    		"value" => $item->getName()
    	));
    	
		$this->createAdd("category","HTMLSelect",array(
			"name" => "item[category]",
			"options" => $this->getCategories(),
			"selected" => $item->getCategory()
		 ));
		 
		 $this->createAdd("is_thumbnail","HTMLModel",array(
		 	"visible" => strlen($item->getImage()) >0
		 ));
		 
		 $this->createAdd("thumbnail","HTMLImage",array(
		 	"src" => SOY_LIST_IMAGE_ACCESS_PATH . $item->getImage(),
		 	"height" => "60px"
		 ));
		 
		 $this->createAdd("delete","HTMLCheckbox",array(
		 	"name" => "delete",
		 	"value" => 1,
		 	"id" => "delete"
		 ));
		 
		 $this->createAdd("image","HTMLInput",array(
		 	"name" => "item[image]",
		 	"value" => $item->getImage()
		 ));
		 
		 $this->createAdd("price","HTMLInput",array(
		 	"name" => "item[price]",
		 	"value" => $item->getPrice()
		 ));
		 
		 $this->createAdd("standard","HTMLInput",array(
		 	"name" => "item[standard]",
		 	"value" => $item->getStandard()
		 ));
		 
		 $this->createAdd("description","HTMLTextArea",array(
		 	"name" => "item[description]",
		 	"value" => $item->getDescription()
		 ));
		 
		 $this->createAdd("url","HTMLInput",array(
		 	"name" => "item[url]",
		 	"value" => $item->getUrl()
		 ));
    }
    
    function getItem($id){
    	try{
    		$item = $this->itemDao->getById($id);
    	}catch(Exception $e){
    		CMSApplication::jump("Item");
    	}
    	return $item;
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