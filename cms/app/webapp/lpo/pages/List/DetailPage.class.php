<?php

class DetailPage extends WebPage{

	private $id;

	function doPost(){
		
		if(soy2_check_token()&&$_POST["Entry"]){
			$dao = SOY2DAOFactory::create("SOYLpo_ListDAO");

			$entry = (object)$_POST["Entry"];
			

			try{
				$oldEntry = $dao->getById($this->id);
			}catch(Exception $e){
				$oldEntry = new SOYLpo_List();
				$oldEntry->setId($this->id);
			}
			
			$entry = SOY2::cast($oldEntry,$entry);
			$entry->setUpdateDate(time());
			
			try{
				$dao->update($entry);
			}catch(Exception $e){
				var_dump($e);
			}
			
			CMSApplication::jump("List.Detail.".$this->id."?updated");
		}
		
	}

    function __construct($args) {
    	$this->id = $args[0];
    	
    	WebPage::WebPage();
    	
    	$config = $this->getConfig();
    	
    	$this->buildForm();
    	
    	$this->createAdd("tiny_mce","HTMLLabel",array(
    		"html" => $this->getTinyMce(),
    		"visible" => ($config->getWisywig())
    	));
    }
    
    function buildForm(){
    	
    	$dao = SOY2DAOFactory::create("SOYLpo_ListDAO");
    	
    	try{
    		$entry = $dao->getById($this->id);
    	}catch(Excepiton $e){
    		CMSApplication::jump("List");
    	}
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("title","HTMLInput",array(
    		"name" => "Entry[title]",
    		"value" => $entry->getTitle(),
    		"readonly" => ($entry->getId()==1)
    	));
    	
    	$this->createAdd("content","HTMLTextArea",array(
    		"name" => "Entry[content]",
    		"value" => $entry->getContent()
    	));
    	
    	$this->createAdd("no_default","HTMLModel",array(
    		"visible" => ($entry->getId()!=1)
    	));
    	
    	$this->createAdd("mode_referer","HTMLCheckBox",array(
    		"name" => "Entry[mode]",
    		"value" => SOYLpo_List::MODE_REFERER,
    		"selected" => ($entry->getMode()==SOYLpo_List::MODE_REFERER),
    		"label" => "リファラ"
    	));
    	
    	$this->createAdd("mode_domain","HTMLCheckBox",array(
    		"name" => "Entry[mode]",
    		"value" => SOYLpo_List::MODE_DOMAIN,
    		"selected" => ($entry->getMode()==SOYLpo_List::MODE_DOMAIN),
    		"label" => "ドメイン"
    	));
    	
    	$this->createAdd("mode_url","HTMLCheckBox",array(
    		"name" => "Entry[mode]",
    		"value" => SOYLpo_List::MODE_URL,
    		"selected" => ($entry->getMode()==SOYLpo_List::MODE_URL),
    		"label" => "URL"
    	));
    	
    	$this->createAdd("keyword","HTMLInput",array(
    		"name" => "Entry[keyword]",
    		"value" => $entry->getKeyword()
    	));
    	
    	$this->createAdd("is_public","HTMLCheckBox",array(
    		"name" => "Entry[isPublic]",
    		"value" => 1,
    		"selected" => ($entry->getIsPublic()==1),
    		"label" => "公開"
    	));
    	
    	$this->createAdd("no_public","HTMLCheckBox",array(
    		"name" => "Entry[isPublic]",
    		"value" => 0,
    		"selected" => ($entry->getIsPublic()==0),
    		"label" => "非公開"
    	));
    }
    
    function getTinyMce(){
    	$html = array();
    	
    	$html[] = "<script type=\"text/javascript\" src=\"".SOY2PageController::createRelativeLink("./webapp/".APPLICATION_ID."/js/tiny_mce/tiny_mce.js")."\"></script>";
    	$html[] = "<script type=\"text/javascript\" src=\"".SOY2PageController::createRelativeLink("./webapp/".APPLICATION_ID."/js/advanced.js")."\"></script>";
    	
    	return implode("\n",$html);
    }
    
    function getConfig(){
    	$dao = SOY2DAOFactory::create("SOYLpo_ConfigDAO");
    	try{
    		$result = $dao->get();
    		$config = $result[0];
    	}catch(Exception $e){
    		$config = new SOYLpo_Config();
    	}
    	
    	return $config;
    }
}
?>