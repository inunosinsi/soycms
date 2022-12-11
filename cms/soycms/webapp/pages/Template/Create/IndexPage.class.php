<?php
SOY2DAOFactory::importEntity("cms.Page");
SOY2DAOFactory::importEntity("cms.Template");
include_once(dirname(__FILE__)."/_stage/base/StageBase.class.php");

class IndexPage extends CMSWebPageBase{

	private $type;
	private $page;

	function doPost(){

		if(soy2_check_token()){
			$contentPage = self::_getContentPage();

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
			self::_saveWizardObject($contentPage->getWizardObj());
		}

		$this->jump("Template.Create");

	}

	function __construct($args) {

		parent::__construct();

		$contentPage = self::_getContentPage();

		$this->addLink("next_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "$('#main_form').attr('action', '" . SOY2PageController::createLink("Template.Create") . "?next'); $('#main_form_submit_button').click();",
			"text" => $contentPage->getNextString(),
			"visible" => strlen($contentPage->getNextString()),
		));

		$this->addLink("prev_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "$('#main_form').attr('action', '" . SOY2PageController::createLink("Template.Create") . "?back'); $('#main_form_submit_button').click();",
			"text" => $contentPage->getBackString(),
			"visible" => strlen($contentPage->getBackString()),
		));

		$this->addLink("end_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "if(confirm('" . CMSMessageManager::get("SOYCMS_TEMPLATE_CONFIRM_EXIT_CREATION") . "')){\$('#main_form').attr('action', '" . SOY2PageController::createLink("Template.Create") . "?end'); $('#main_form_submit_button').click();}",
			"text" => CMSMessageManager::get("SOYCMS_TEMPLATE_CANCEL"),
			"visible" => strlen($contentPage->getNextString()),
		));

		$this->addModel("display_footer",array(
				"visible" => strlen($contentPage->getBackString()) || strlen($contentPage->getNextString()),
		));

		$this->add("content",$contentPage);

		$this->addForm("main_form");

		$this->addLabel("stage_title", array(
				"text" => $contentPage->getStageTitle(),
		));

		self::_addEditorJS();
		self::_addFileManagerJS();
	}

	private function _addEditorJS(){

		$currentStage = self::_detectStages();

		$this->addModel("PanelManager.js",array(
				"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js"),
				"visible" => ( $currentStage == "TemplateEditStage" ),
		));

		$this->addModel("TemplateEditor",array(
				"src" => SOY2PageController::createRelativeLink("./js/editor/template_editor.js"),
				"visible" => ( $currentStage == "TemplateEditStage" ),
		));

		//CSS保存先URLをJavaScriptに埋め込みます
		$this->addLabel("cssurl",array(
				"type"=>"text/JavaScript",
				"html"=>'var cssURL = "'.SOY2PageController::createLink("Page.Editor").'";' .
				'var siteId="'.UserInfoUtil::getSite()->getSiteId().'";' .
				'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
				'var siteURL = "'.UserInfoUtil::getSiteUrl().'";',
				"visible" => ( $currentStage == "TemplateEditStage" ),
		));

		$this->addModel("cssmenu",array(
				"type" => "text/JavaScript",
				"src" => SOY2PageController::createRelativeLink("js/editor/cssMenu.js"),
				"visible" => ( $currentStage == "TemplateEditStage" ),
		));
	}

	private function _addFileManagerJS(){
		$currentStage = self::_detectStages();

		$this->createAdd("add_file_list_url","HTMLScript",array(
				"type" => "text/JavaScript",
				"html" => "var add_file_list_url = '". SOY2PageController::createLink("FileManager.FileAction") . "/15/';",
				"visible" => ( $currentStage == "FileSettingStage" ),
		));

		$this->addModel("filemanager",array(
				"visible" => ( $currentStage == "FileSettingStage" ),
		));

	}

	private function _getContentPage(){

		$wizObj = self::_getWizardObject();
		
		if(!empty($wizObj) && @!is_null($wizObj->template)){
			$currentStage = self::_detectStages();
		}else{
			$currentStage = "StartStage";
		}
		
		if(CMSUtil::isPageTemplateEnabled() === false){
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

	private function _detectStages(){
		$sessionStage = SOY2ActionSession::getUserSession()->getAttribute("Template.Create.WizardCurrentStage");
		return (is_string($sessionStage)) ? $sessionStage : "StartStage";
	}

	private function _getWizardObject(){
		$wizObj = SOY2ActionSession::getUserSession()->getAttribute("Template.Create.WizardObject");
		$wizObj = (isset($wizObj)) ? @unserialize($wizObj) : new StdClass();
		if(is_null($wizObj)) $wizObj = new StdClass();
		return $wizObj;
	}

	private function _saveWizardObject($wizObj){
		SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardObject",serialize($wizObj));
	}
}
