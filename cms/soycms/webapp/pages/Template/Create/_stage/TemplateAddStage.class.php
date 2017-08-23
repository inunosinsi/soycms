<?php

class TemplateAddStage extends StageBase{

	public function getStageTitle(){
		return "ページ雛形の新規作成 (2/5)";
	}

	public function execute(){

		$this->createAdd("import","HTMLCheckbox",array(
			"name" => "operation",
			"value" => "import",
			"label" => CMSMessageManager::get("SOYCMS_TEMPLATE_CREATE_FROM_WEBPAGE"),
			"type" => "radio"
		));

		$this->createAdd("create","HTMLCheckbox",array(
			"name" => "operation",
			"value" => "create",
			"label" => CMSMessageManager::get("SOYCMS_TEMPLATE_CREATE_NEW_WEBPAGE"),
			"selected" => true,
			"type" => "radio"
		));

		$this->createAdd("name","HTMLInput",array(
			"name"=>"name",
			"value"=>""
		));

		$dao = SOY2DAOFActory::create("cms.PageDAO");
		$pages = $dao->get();

		$pageList = array();

		foreach($pages as $page){
			if($page->getPageType() == Page::PAGE_TYPE_NORMAL){
				$pageList[$page->getId()] = htmlspecialchars($page->getTitle(), ENT_QUOTES, "UTF-8");
			}
		}

		$this->createAdd("page_list","HTMLSelect",array(
			"name"=>"page_id",
			"options"=>$pageList
		));

		$dao = null;

	}

	//次へが押された際の動作
	public function checkNext(){

		$operation = @$_POST["operation"];
		$pageId = @$_POST["page_id"];
		$name = @$_POST["name"];

		if(is_null($name) || strlen($name) == 0){
			$this->addMessage("TEMPLATE_FILE_NAME_IS_BLANK");
			return false;
		}

		$tmpDir = $this->getTempDir();

		$dao = SOY2DAOFactory::create("cms.PageDAO");
		$id = md5(mt_rand());

		if($operation == "import"){

			try{
				$page = $dao->getById($pageId);
			}catch(Exception $e){
				$this->addMessage("TEMPLATE_GET_PAGE_ERROR");
				return false;
			}

			file_put_contents($tmpDir ."/". $id , $page->getTemplate());

			$this->wizardObj->template->addTemplate(array(
				$id => array(
					"name" => $name,
				)
			));

		}else{
			file_put_contents($tmpDir ."/". $id , "");
			$this->wizardObj->template->addTemplate(array(
				$id => array(
					"name" => $name,
				)
			));
		}

		$this->wizardObj->currentEditTemplateId = $id;

		return true;
	}

	//前へが押された際の動作
	public function checkBack(){
		return true;
	}

	//次のオブジェクト名、終了の際はEndStageを呼び出す
	public function getNextObject(){
		return "TemplateEditStage";
	}

	//前のオブジェクト名、nullの場合は表示しない
	public function getBackObject(){
		if($this->wizardObj->template->getPageType() == Page::PAGE_TYPE_NORMAL){
			return "StartStage";
		}

		return "TemplateSettingStage";
	}

	public function getNextString(){
		return CMSMessageManager::get("SOYCMS_WIZARD_NEXT");
	}

	public function getBackString(){
		$template = $this->wizardObj->template->getTemplate();

		if(empty($template)){
			if($this->wizardObj->template->getPageType() == Page::PAGE_TYPE_NORMAL){
				return CMSMessageManager::get("SOYCMS_WIZARD_PREV");
			}

			return "";
		}else{
			return CMSMessageManager::get("SOYCMS_WIZARD_PREV");
		}
	}
}
