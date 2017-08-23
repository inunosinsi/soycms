<?php

class EditPage extends CMSWebPageBase{

	private $id;
	private $template;
	private $file;
	
	function doPost(){
    	if(soy2_check_token()){
			$result = $this->run("Template.UpdateAction",array("id"=>$this->id,"file"=>$this->file));
			if($result->success()){
				$this->addMessage("TEMPLATE_UPDATE_SUCCESS");
			}else{
				$this->addMessage("TEMPLATE_UPDATE_FAILED");
			}
			
			$this->jump("Template.Edit.".$this->id.".".$this->file);
    	}
		
	}

    function __construct($arg) {
    	
    	$this->id = $arg[0];
    	$file = $arg[1];
    	
    	$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
    	
    	
    	try{
    		$template = $logic->getById($this->id);
    	}catch(Exception $e){
    		$this->jump("Template");
    	}
    	
    	$this->file = $file;
    	$this->template = $template;
    	
    	$template_files = $template->getTemplate();
    	
    	$array = $template_files[$file];
    	
    	parent::__construct();
    	
    	$this->createAdd("back_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Template.Detail.".$this->id)
    	));
    	
    	$this->createAdd("name","HTMLLabel",array(
    		"text" => $array["name"]
    	));
    	
    	$this->createAdd("description","HTMLLabel",array(
    		"text" => @$array["description"]
    	));
    	
    	$this->createAdd("template","HTMLTextArea",array(
    		"name" => "template",
    		"value" => file_get_contents($template->getTemplatesDirectory() ."/" . $file)
    	));
    	
    	$this->createAdd("template_editor","HTMLModel",array(
    		"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
    		"onload" => "init_template_editor();"
    	));
    	
    	$this->addModel("PanelManager.js",array(
			"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js")
		));
		
		$this->addModel("TemplateEditor",array(
			"src" => SOY2PageController::createRelativeLink("./js/editor/template_editor.js") 
		));
		
		HTMLHead::addLink("editor",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/editor/editor.css")
		));
		
		HTMLHead::addLink("section",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/form.css")
		));

		HTMLHead::addLink("form",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.css")
		));
		
		//CSS保存先URLをJavaScriptに埋め込みます
		$this->addModel("cssurl",array(
			"type"=>"text/JavaScript",
			"script"=>'var cssURL = "'.SOY2PageController::createLink("Page.Editor").'";' .
					  'var siteId="'.UserInfoUtil::getSite()->getSiteId().'";' .
					  'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
					  'var siteURL = "'.UserInfoUtil::getSiteUrl().'";'
		));
		
		$this->addModel("cssmenu",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("js/editor/cssMenu.js")
		));
		
		$this->createAdd("edit_form","HTMLForm");
			
    
    }
}
?>