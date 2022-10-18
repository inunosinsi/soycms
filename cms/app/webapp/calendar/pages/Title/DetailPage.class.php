<?php

class DetailPage extends WebPage{

	private $id;
	private $error=false;
	private $dao;

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["title"]) && self::_check($_POST["title"])){
				try{
					$old= $this->dao->getById($this->id);
				}catch(Exception $e){
					$old = new SOYCalendar_Title();
				}
	
				$title = SOY2::cast($old, (object)$_POST["title"]);
				if($this->id > 0){	//更新
					try{
						$this->dao->update($title);
					}catch(Exception $e){
						var_dump($e);
					}
				} else {	//新規
					try{
						$this->dao->insert($title);
					}catch(Exception $e){
						var_dump($e);
					}
				}
				
				CMSApplication::jump("Title");
			}
		}

		$this->error = true;
	}

	/**
	 * @param array
	 * @return bool
	 */
	private function _check(array $title){
		return (strlen($title["title"]) > 0);
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : 0;

    	$this->dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
    	try{
    		$title = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		$title = new SOYCalendar_Title();
    	}

    	parent::__construct();

		DisplayPlugin::toggle("error", $this->error);
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

		DisplayPlugin::toggle("register_button_area", $this->id === 0);
		DisplayPlugin::toggle("update_button_area", $this->id > 0);
    }
}
