<?php

class DetailPage extends WebPage{

	private $id;
	private $error;
	private $dao;
	
	function doPost(){
		
		if(soy2_check_token()&&$this->check($_POST["title"])==true){
			
			try{
				$oldTitle = $this->dao->getById($this->id);
			}catch(Exception $e){
				var_dump($e);
			}
			
			$title = SOY2::cast($oldTitle,(object)$_POST["title"]);
			$title->setUpdateDate(time());
			
			try{
				$this->dao->update($title);
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

    function __construct($args) {
    	
    	$this->id = $args[0];
    	
    	$this->dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
    	try{
    		$title = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		$title = new SOYCalendar_Title();
    	}
    	
    	WebPage::WebPage();
    	
    	$this->createAdd("error","HTMLModel",array(
    		"visible" => $this->error == true
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("title","HTMLInput",array(
    		"name" => "title[title]",
    		"value" => $title->getTitle()
    	));
    	$this->createAdd("attribute","HTMLInput",array(
    		"name" => "title[attribute]",
    		"value" => $title->getAttribute()
    	));
    	
    	$this->createAdd("create_date","HTMLInput",array(
    		"name" => "title[createDate]",
    		"value" => $title->getCreateDate()
    	));
    }
}
?>