<?php

class UnInstallPage extends CMSWebPageBase{
	
	var $id;
	
	function doPost(){
		
    	if(soy2_check_token()){
			$id = $this->id;
			
			$fileList = @$_POST["fileList"];
			$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
			
			try{
	    		$template = $logic->uninstallTemplate($id,@$_POST["fileList"]);
	    	}catch(Exception $e){
	    		$this->jump("Template");
	    	}  	
			
	    	$this->jump("Template");
    	}
	}
	
    function __construct($args) {
    	$id = $args[0];
    	$this->id = $id;
    	
    	parent::__construct();
    	    	
    	$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
    	
    	try{
    		$template = $logic->getById($id);
    		if(!$template->isActive())throw new Exception("未インストール");
    	}catch(Exception $e){
    		$this->jump("Template");
    	}
    	
    	$this->createAdd("template_name","HTMLLabel",array(
    		"text" => $template->getName()
    	));
    	
    	$this->createAdd("file_list","FileList",array(
    		"list" => $template->getFileList()
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	
    }
}

class FileList extends HTMLList{
	
	function populateItem($entity){
		
		$this->createAdd("check","HTMLCheckBox",array(
			"value" => $entity["name"],
			"name" => "fileList[]",
			"selected" => true
		));
		
		$path = $entity["path"];
		
		$flag = false;
		if($path[0] == "/"){
			if(file_exists(UserInfoUtil::getSiteDirectory().substr($path,1))){
				$flag = true;
			}	
		}else{
			if(file_exists(UserInfoUtil::getSiteDirectory().$path)){
				$flag = true;
			}			
		}
		
		
		$this->createAdd("name","HTMLLabel",array(
			"text" => $path,
			"style" => (!$flag) ? "color:red;" : ""
		));
		
		$this->createAdd("description","HTMLLabel",array(
			"text" => $entity["description"]
		));
	}
	
}
?>