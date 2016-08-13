<?php

class TemplateEditStage extends StageBase{

	protected $id;

    function TemplateEditStage() {
    	WebPage::__construct();	
    }
    
    function setWizardObj($obj){
    	parent::setWizardObj($obj);
    	
    	$this->id = @$_GET["id"];
    	if($this->id){
    		$this->wizardObj->currentEditTemplateId = $this->id;
    		$this->saveWizardObject();
    	}else{
    		$this->id = $this->wizardObj->currentEditTemplateId;
    	}
    }
    
    function execute(){
    	
    	$array = $this->wizardObj->template->getTemplateById($this->id);
    	
    	$this->createAdd("name","HTMLLabel",array(
    		"text" => $array["name"]
    	));
    	
    	$this->createAdd("description","HTMLTextArea",array(
    		"name" => "description",
			"value" => @$array["description"]
    	));
    	
    	$this->createAdd("template","HTMLTextArea",array(
    		"name" => "template",
    		"value" => file_get_contents($this->getTempDir() ."/" . $this->id)
    	));
    	
    	$this->createAdd("template_editor","HTMLModel",array(
    		"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
    		"onload" => "init_template_editor();"
    	));
    	
    	HTMLHead::addScript("PanelManager.js",array(
			"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js")
		));
		
		HTMLHead::addScript("TemplateEditor",array(
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
		HTMLHead::addScript("cssurl",array(
			"type"=>"text/JavaScript",
			"script"=>'var cssURL = "'.SOY2PageController::createLink("Page.Editor").'";' .
					  'var siteId="'.UserInfoUtil::getSite()->getSiteId().'";' .
					  'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
					  'var siteURL = "'.UserInfoUtil::getSiteUrl().'";'
		));
		
		HTMLHead::addScript("cssmenu",array(
				"type" => "text/JavaScript",
				"src" => SOY2PageController::createRelativeLink("js/editor/cssMenu.js")
			));
			
		
    }
    
    //次へが押された際の動作
    function checkNext(){
    	$array = $this->wizardObj->template->getTemplate();
    	
    	$array[$this->id]["description"] = $_POST["description"];
    	file_put_contents($this->getTempDir() ."/" . $this->id,$_POST["template"]);
    	
    	$this->wizardObj->template->setTemplate($array);
    	
    	return true;
    }
    
    //前へが押された際の動作
    function checkBack(){
    	return true;
    }
    
    //次のオブジェクト名、終了の際はEndStageを呼び出す
    function getNextObject(){
    	return "TemplateSettingStage";
    }
    
    //前のオブジェクト名、nullの場合は表示しない
    function getBackObject(){
    	return "TemplateSettingStage";
    }

    function getNextString(){
    	return CMSMessageManager::get("SOYCMS_WIZARD_NEXT");
    }
    
    function getBackString(){
    	return CMSMessageManager::get("SOYCMS_WIZARD_PREV");
    }
}
?>