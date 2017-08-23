<?php

class TemplateEditStage extends StageBase{

	protected $id;

	public function getStageTitle(){
		return "ページ雛形の新規作成 (2/5) - テンプレートの編集";
	}

	public function setWizardObj($obj){
		parent::setWizardObj($obj);

		$this->id = @$_GET["id"];
		if($this->id){
			$this->wizardObj->currentEditTemplateId = $this->id;
			$this->saveWizardObject();
		}else{
			$this->id = $this->wizardObj->currentEditTemplateId;
		}
	}

	public function execute(){

		$array = $this->wizardObj->template->getTemplateById($this->id);

		$this->createAdd("name","HTMLInput",array(
			"value" => $array["name"],
			"disabled" => true,
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

	}

	//次へが押された際の動作
	public function checkNext(){
		$array = $this->wizardObj->template->getTemplate();

		$array[$this->id]["description"] = $_POST["description"];
		file_put_contents($this->getTempDir() ."/" . $this->id,$_POST["template"]);

		$this->wizardObj->template->setTemplate($array);

		return true;
	}

	//前へが押された際の動作
	public function checkBack(){
		return true;
	}

	//次のオブジェクト名、終了の際はEndStageを呼び出す
	public function getNextObject(){
		return "TemplateSettingStage";
	}

	//前のオブジェクト名、nullの場合は表示しない
	public function getBackObject(){
		return "TemplateSettingStage";
	}

	public function getNextString(){
		return CMSMessageManager::get("SOYCMS_WIZARD_NEXT");
	}

	public function getBackString(){
		return CMSMessageManager::get("SOYCMS_WIZARD_PREV");
	}
}
