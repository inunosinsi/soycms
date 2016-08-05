<?php

class CreatePage extends WebPage{

	private $error;
	
	function doPost(){
		
		$title = $_POST["title"];
		
		if(soy2_check_token()&&$this->check($title)==true){
			
			$title = SOY2::cast("domain.SOYCalendar_Title",$title);
			
			$title->setCreateDate(time());
			$title->setUpdateDate(time());
			
			$dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
			try{
				$dao->insert($title);
				CMSApplication::jump("Title");
			}catch(Exception $e){
				var_dump($e);
				
			}
		}
		
		$this->error = true;
		
	}
	
	function check($title){
		return (strlen($title["title"])>0)?true:false;
	}

    function __construct() {
    	WebPage::WebPage();
    	
    	$this->createAdd("error","HTMLModel",array(
    		"visible" => $this->error == true
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("title","HTMLInput",array(
    		"name" => "title[title]",
    		"value" => @$_POST["title"]["title"]
    	));
    	
    	$this->createAdd("attribute","HTMLInput",array(
    		"name" => "title[attribute]",
    		"value" => @$_POST["title"]["attribute"]
    	));
    }
}
?>