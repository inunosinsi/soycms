<?php
SOY2DAOFactory::importEntity("cms.Page");
SOY2DAOFactory::importEntity("cms.Template");
include_once(dirname(__FILE__)."/_stage/base/StageBase.class.php");

class IndexPage extends CMSWebPageBase{
	
	private $type;
	private $page;
	
	function doPost(){
		
    	if(soy2_check_token()){
			$contentPage = $this->getContentPage();
			
			if(isset($_GET["next"])){
				if($contentPage->checkNext()){
					SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage",$contentPage->getNextObject());
				}else{
					//エラーの時の処理をどうしよう
				}
			}
			
			if(isset($_GET["back"])){
				if($contentPage->checkBack()){
					SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage",$contentPage->getBackObject());
				}else{
					//エラーの時の処理をどうしよう
				}
			}
			
			if(isset($_GET["end"])){
				$contentPage->deleteTempDir();
		    	$contentPage->wizardObj = null;
		    	$contentPage->saveWizardObject();
		    	
		    	$this->jump("Template");
			}
			
			//データを保存
			$this->saveWizardObject($contentPage->getWizardObj());
    	}
		
		$this->jump("Template.Create");
		
	}
	
    function __construct($args) {
    	
    	WebPage::WebPage();
		
		$contentPage = $this->getContentPage();
				
		$this->createAdd("next_link", "HTMLLink", array(
			"link" => "javascript:void(0);",
			"onclick" => "$('#main_form').attr('action', '" . SOY2PageController::createLink("Template.Create") . "?next'); $('#main_form_submit_button').click();",
			"text" => $contentPage->getNextString(),
			"visible" => strlen($contentPage->getNextString()) != 0,
		));
		
		$this->createAdd("prev_link", "HTMLLink", array(
			"link" => "javascript:void(0);",
			"onclick" => "$('#main_form').attr('action', '" . SOY2PageController::createLink("Template.Create") . "?back'); $('#main_form_submit_button').click();",
			"text" => $contentPage->getBackString(),
			"visible" => strlen($contentPage->getBackString()) != 0,
		));
		
		$this->createAdd("end_link", "HTMLLink", array(
			"link" => "javascript:void(0);",
			"onclick" => "if(confirm('" . CMSMessageManager::get("SOYCMS_TEMPLATE_CONFIRM_EXIT_CREATION") . "')){\$('#main_form').attr('action', '" . SOY2PageController::createLink("Template.Create") . "?end'); $('#main_form_submit_button').click();}",
			"text" => CMSMessageManager::get("SOYCMS_TEMPLATE_CANCEL"),
			"visible" => strlen($contentPage->getNextString()) != 0,
		));
		
		$this->add("content",$contentPage);
		
		$this->createAdd("main_form","HTMLForm");
    }   
    
   function getContentPage(){
    	
    	$wizObj = $this->getWizardObject();
    	
    	if(!empty($wizObj) && @!is_null($wizObj->template)){
    		$currentStage = $this->detectStages();
    	}else{
    		$currentStage = "StartStage";
    	}
    	
    	if(CMSUtil::checkZipEnable() === false){
    		$currentStage = "FailedStage";
    	}
    	    	
		$stageClassName = "Template.Create._stage.".$currentStage;
		try{
			$page = $this->create("content",$stageClassName);
		}catch(Exception $e){
			$page = $this->create("content","Template.Create._stage.EndStage");
		}
		
		$page->setWizardObj($wizObj);
		
		return $page;
    }
    
    function detectStages(){
    	$sessionStage = SOY2ActionSession::getUserSession()->getAttribute("Template.Create.WizardCurrentStage");
    	
    	if(is_null($sessionStage)){
			return "StartStage";
		}else{
			return $sessionStage;
		}
    }
    
    function getWizardObject(){
    	$wizObj = SOY2ActionSession::getUserSession()->getAttribute("Template.Create.WizardObject");
    	
    	if(is_null($wizObj)){
    		$wizObj = new StdClass();
    	}else{
    		$wizObj = unserialize($wizObj);
    	}
    	
    	return $wizObj;
    }
    
    function saveWizardObject($wizObj){
    	SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardObject",serialize($wizObj));
    }
}
?>