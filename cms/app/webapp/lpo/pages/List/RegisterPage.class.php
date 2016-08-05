<?php

class RegisterPage extends WebPage{

	private $entry;

	function doPost(){
		
		if(soy2_check_token()&&$_POST["Entry"]){
			$entry = $_POST["Entry"];
			
			$dao = SOY2DAOFactory::create("SOYLpo_ListDAO");
			
			$entry = SOY2::cast("SOYLpo_List",$entry);
			
			//コンテンツと公開状況はnot nullなので、0を挿入しておく
			$entry->setContent("");
			$entry->setIsPublic(0);
			
			//登録日と更新日に今の時間を入れる
			$entry->setCreateDate(time());
			$entry->setUpdateDate(time());
			
			try{
				$dao->insert($entry);
				CMSApplication::jump("List");
			}catch(Exception $e){
			}
			
			//登録に失敗したとき
			$this->entry = $entry;
		}
		
	}

    function __construct() {
    	WebPage::WebPage();
   	
    	$this->buildForm();
    }
    
    function buildForm(){
    	
    	$dao = SOY2DAOFactory::create("SOYLpo_ListDAO");
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("title","HTMLInput",array(
    		"name" => "Entry[title]",
    		"value" => ""
    	));
    	
    	$this->createAdd("mode_referer","HTMLCheckBox",array(
    		"name" => "Entry[mode]",
    		"value" => SOYLpo_List::MODE_REFERER,
    		"selected" => true,
    		"label" => "リファラ"
    	));
    	
    	$this->createAdd("mode_domain","HTMLCheckBox",array(
    		"name" => "Entry[mode]",
    		"value" => SOYLpo_List::MODE_DOMAIN,
    		"selected" => false,
    		"label" => "ドメイン"
    	));
    	
    	$this->createAdd("mode_url","HTMLCheckBox",array(
    		"name" => "Entry[mode]",
    		"value" => SOYLpo_List::MODE_URL,
    		"selected" => false,
    		"label" => "URL"
    	));
    	
    	$this->createAdd("keyword","HTMLInput",array(
    		"name" => "Entry[keyword]",
    		"value" => ""
    	));
    	
    }    
}
?>