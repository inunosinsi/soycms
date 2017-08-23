<?php

class EditHtaccessPage extends CMSUpdatePageBase {

	const FILENAME = ".htaccess";
	var $id;
	
	function doPost(){
		
		if(soy2_check_token()){
			if($this->id == $_POST["site_id"] && $this->saveFile($_POST["contents"])){
				$this->addMessage("UPDATE_SUCCESS");
			}
			
			$this->reload();
		}
			
		exit;
	}

    function __construct($args) {
    	if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("Site");
    	}
    	$id = (isset($args[0])) ? $args[0] : null;
    	$this->id = $id;
    	
		parent::__construct();
		
    	HTMLHead::addLink("site.edit.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/site/edit.css") . "?" . SOYCMS_BUILD_TIME
		));

		$site = $this->getSite();
		
		$this->addLabel("site_name", array(
			"text" => $site->getSiteName()
		));
		
		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Site.Detail." . $this->id),
		));

		$this->addForm("update_site_form", array(
			"disabled" => !is_writable($site->getPath() . self::FILENAME)
		));
		
		$this->addInput("site_id", array(
			"type"  => "hidden",
			"name"  => "site_id",
			"value" => $this->id 
		));
		
		$this->addTextArea("contents", array(
			"name"  => "contents",
			"value" => @file_get_contents($site->getPath() . self::FILENAME),
			"disabled" => !is_writable($site->getPath() . self::FILENAME)
		));
		
		$this->addInput("button", array(
			"value"     => CMSMessageManager::get("SOYCMS_SAVE"),
			"disabled" => !is_writable($site->getPath() . self::FILENAME)
		));
    	
		if(!is_writable($site->getPath() . self::FILENAME)){
			$this->addErrorMessage("SOYCMS_NOT_WRITABLE");
		}
		$messages = CMSMessageManager::getMessages();
		$errores = CMSMessageManager::getErrorMessages();
    	$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => (count($messages) > 0)
		));
		$this->addLabel("error", array(
			"text" => implode($errores),
			"visible" => (count($errores) > 0)
		));
    }
    
    function saveFile($contents){
    	$site = $this->getSite();
    	return file_put_contents($site->getPath() . self::FILENAME, $contents);
    }
    
    function getSite(){
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Site");
		}
		
		return $site;
    }
    
}
