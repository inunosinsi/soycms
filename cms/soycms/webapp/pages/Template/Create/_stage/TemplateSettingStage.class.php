<?php

class TemplateSettingStage extends StageBase{

	public function getStageTitle(){
		return "ページ雛形の新規作成 (2/5) - テンプレートの管理";
	}

	function __construct() {

		if(isset($_GET["add"])){
			SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage","TemplateAddStage");
			$this->jump("Template.Create");
		}

		if(isset($_GET["edit"])){
			SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage","TemplateEditStage");
			$this->jump("Template.Create"."?id=".$_GET["id"]);
		}

		if(isset($_GET["delete"])){
			SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage","TemplateDeleteStage");
			$this->jump("Template.Create"."?id=".$_GET["id"]);
		}

		parent::__construct();

	}

	public function execute(){

		//追加リンク
		$this->createAdd("add_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Template.Create") . "?add",
			"visible" => ($this->wizardObj->template->getPageType() != Page::PAGE_TYPE_BLOG)
		));

		$this->createAdd("template_list","TemplateList",array(
			"list" => $this->wizardObj->template->getTemplate(),
			"template" => $this->wizardObj->template
		));

	}


	public function checkNext(){
		$templates = $this->wizardObj->template->getTemplate();

		if(empty($templates)){
			return false;
		}

		return true;
	}

	//前へが押された際の動作
	public function checkBack(){
		return true;
	}

	//次のオブジェクト名、終了の際はEndStageを呼び出す
	public function getNextObject(){

		$templates = $this->wizardObj->template->getTemplate();

		if(empty($templates)){
			return "";
		}

		return "FileSettingStage";
	}

	//前のオブジェクト名、nullの場合は表示しない
	public function getBackObject(){
		return "StartStage";
	}
}

class TemplateList extends HTMLList{

	private $template;

	public function setTemplate($template){
		$this->template = $template;
	}

	protected function populateItem($entity,$key){

		$this->createAdd("name","HTMLLabel",array(
			"text" => @$entity["name"]
		));

		$this->createAdd("description","HTMLLabel",array(
			"text" => @$entity["description"]
		));


		$html = array();
		$html[] = '<a class="btn btn-default btn-sm" href="'.htmlspecialchars(SOY2PageController::createLink("Template.Create").'?edit&id='.$key, ENT_QUOTES, "UTF-8").'">'.htmlspecialchars(CMSMessageManager::get("SOYCMS_EDIT"), ENT_QUOTES, "UTF-8").'</a>';

		if($this->template->getPageType() != Page::PAGE_TYPE_BLOG){
			$html[] = '<a class="btn btn-default btn-sm" href="'.htmlspecialchars(SOY2PageController::createLink("Template.Create").'?delete&id='.$key, ENT_QUOTES, "UTF-8").'">'.htmlspecialchars(CMSMessageManager::get("SOYCMS_DELETE"), ENT_QUOTES, "UTF-8").'</a>';
		}

		$this->createAdd("operation","HTMLLabel",array(
			"html" => implode("&nbsp;",$html)
		));

	}

}
