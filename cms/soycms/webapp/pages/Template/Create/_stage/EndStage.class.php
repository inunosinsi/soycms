<?php
class EndStage extends StageBase{

    function EndStage() {
    	WebPage::WebPage();
    }
    
    function execute(){
    	//一時ディレクトリの削除
    	$this->deleteTempDir();
    	$this->wizardObj = null;
    	
    	$this->saveWizardObject();
    	
    	SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage",null);
    }
    
    function checkNext(){
    	return false;
    }
    
    function checkBack(){
    	return false;
    }
    
    function getNextString(){
    	return "";
    }
    
    function getBackString(){
    	return "";
    }
}
?>