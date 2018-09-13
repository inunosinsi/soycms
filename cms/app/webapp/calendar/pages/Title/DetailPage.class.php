<?php

class DetailPage extends WebPage{

	private $id;
	private $error;
	private $dao;
	
	function doPost(){
		
		if(soy2_check_token() && self::check($_POST["title"])){
			
			try{
				$oldTitle = $this->dao->getById($this->id);
			}catch(Exception $e){
				var_dump($e);
			}
			
			$title = SOY2::cast($oldTitle,(object)$_POST["title"]);
			
			try{
				$this->dao->update($title);
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

    function __construct($args) {
    	
    	$this->id = $args[0];
    	
    	$this->dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
    	try{
    		$title = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		$title = new SOYCalendar_Title();
    	}
    	
    	parent::__construct();
    	
    	$this->addModel("error", array(
    		"visible" => ($this->error == true)
    	));
    	
    	$this->addForm("form");
    	
    	$this->addInput("title", array(
    		"name" => "title[title]",
    		"value" => $title->getTitle()
    	));
    	$this->addInput("attribute", array(
    		"name" => "title[attribute]",
    		"value" => $title->getAttribute()
    	));
    	
    	$this->addInput("create_date", array(
    		"name" => "title[createDate]",
    		"value" => $title->getCreateDate()
    	));
    }
}
?>