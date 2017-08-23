<?php

class StartStage extends StageBase{

	public function getStageTitle(){
		return "ページ雛形の新規作成 (1/5)";
	}

	public function execute(){

		$template = @$this->wizardObj->template;
		if(!$template){
			$template = SOY2DAOFactory::create("cms.Template");
			$template->setPageType(Page::PAGE_TYPE_NORMAL);
		}

		$this->createAdd("template_name","HTMLInput",array(
			"name" => "template_name",
			"value" => $template->getName()
		));

		$this->createAdd("template_id","HTMLInput",array(
			"name" => "template_id",
			"value" => $template->getId()
		));

		$this->createAdd("template_type_normal","HTMLCheckBox",array(
			"name" => "pageType",
			"label" => CMSMessageManager::get("SOYCMS_NORMALPAGE"),
			"value" => Page::PAGE_TYPE_NORMAL,
			"selected" => ($template->getPageType() == Page::PAGE_TYPE_NORMAL),
			"type" => "radio"
		));

		$this->createAdd("template_type_blog","HTMLCheckBox",array(
			"name" => "pageType",
			"label" => CMSMessageManager::get("SOYCMS_BLOGPAGE"),
			"value" => Page::PAGE_TYPE_BLOG,
			"selected" => ($template->getPageType() == Page::PAGE_TYPE_BLOG),
			"type" => "radio"
		));

		$this->createAdd("template_description","HTMLTextArea",array(
			"name" => "template_description",
			"value" => $template->getDescription()
		));

	}

	public function checkNext(){

		if(isset($this->wizardObj->template)){
			//一時ディレクトリを削除
			$this->deleteTempDir();
		}

		//Objectに値の投入
		$template = SOY2DAOFactory::create("cms.Template");
		$template->setId($_POST["template_id"]);
		$template->setName($_POST["template_name"]);
		$template->setPageType($_POST["pageType"]);
		$template->setDescription($_POST["template_description"]);

		$this->wizardObj->template = $template;

		//ヴァリデート
		if(is_null($template->getId())){
			$this->addErrorMessage("TEMPLATE_ID_IS_BLANK");
			return false;
		}

		if(is_null($template->getName())){
			$this->addErrorMessage("TEMPLATE_NAME_ID_BLANK");
			return false;
		}

		if(is_null($template->getPageType())){
			$this->addErrorMessage("TEMPLATE_TYPE_ID_INVALID");
			return false;
		}

		//テンプレートのIDは英数字のみ
		if(!preg_match('/^[a-z]+[a-z0-9_]*$/',$template->getId())){
			$this->addErrorMessage("TEMPLATE_ID_INVALID");
			return false;
		}

		//一時ディレクトリを作成
		$dir = $this->getTempDir();

		return true;
	}

	public function checkBack(){
		return true;
	}

	public function getNextObject(){
		switch($this->wizardObj->template->getPageType()){

			case Page::PAGE_TYPE_BLOG:
				return "BlogTemplateSettingStage";
				break;
			case Page::PAGE_TYPE_NORMAL:
			default:
				return "TemplateAddStage";
				break;


		}
	}

	public function getBackObject(){
		return null;
	}

	public function getBackString(){
		return "";
	}
}
