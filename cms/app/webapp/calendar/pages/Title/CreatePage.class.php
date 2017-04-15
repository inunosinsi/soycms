<?php

class CreatePage extends WebPage{

	private $error;
	
	function doPost(){
		
		$title = $_POST["title"];
		
		if(soy2_check_token() && self::check($title)){
			
			$title = SOY2::cast("domain.SOYCalendar_Title",$title);
			
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
	
	private function check($title){
		return (strlen($title["title"])>0);
	}

    function __construct() {
    	WebPage::__construct();
    	
    	$this->addModel("error", array(
    		"visible" => ($this->error == true)
    	));
    	
    	$this->addForm("form");
    	
    	$this->addInput("title", array(
    		"name" => "title[title]",
    		"value" => (isset($_POST["title"]["title"])) ? $_POST["title"]["title"] : ""
    	));
    	
    	$this->addInput("attribute", array(
    		"name" => "title[attribute]",
    		"value" => (isset($_POST["title"]["attribute"])) ? $_POST["title"]["attribute"] : ""
    	));
    }
}
?>