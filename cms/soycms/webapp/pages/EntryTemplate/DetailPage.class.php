<?php

class DetailPage extends CMSWebPageBase{

	private $id;

	function doPost(){
		if(soy2_check_token()){

			$result = SOY2ActionFactory::createInstance("EntryTemplate.UpdateAction")->run();
			if($result->success()){
				if($result->getAttribute("mode") == "create"){
					$this->addMessage("ENTRY_TEMPLATE_CREATE_SUCCESS");
				}else{
					$this->addMessage("ENTRY_TEMPLATE_SAVE_SUCCESS");
				}
			}else{
				if($result->getAttribute("mode") == "create"){
					$this->addMessage("ENTRY_TEMPLATE_CREATE_FAILED");
				}else{
					$this->addMessage("ENTRY_TEMPLATE_SAVE_FAIELD");
				}
			}

			if($this->id){
				$this->jump("EntryTemplate.Detail.".$this->id);
			}else{
				$this->jump("EntryTemplate");
			}
		}
	}

	function __construct($arg) {

		$id = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;
		parent::__construct();

		$this->addLabel("page_title", array(
				"text" => (is_numeric($id)) ? CMSMessageManager::get("SOYCMS_ENTRY_TEMPLATE_DETAIL") : CMSMessageManager::get("SOYCMS_CREATE_NEW")
		));

		$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateDetailAction",array("id"=>$id))->run();
		$template = $result->getAttribute("entity");
		$this->addInput("template_id", array(
			"value"=>$template->getId(),
			"name"=>"template_id"
		));
		$this->addInput("name", array(
			"value"=>$template->getName(),
			"name"=>"name"
		));

		$this->addTextArea("description", array(
			"text"=>$template->getDescription(),
			"name"=>"description"
		));
		$temp = $template->getTemplates();
		$this->addTextArea("content", array(
			"text" => (isset($temp['content'])) ? $temp['content'] : "",
			"name"=>"templates[content]"
		));
		$this->addTextArea("more", array(
			"text" => (isset($temp['more'])) ? $temp['more'] : "",
			"name"=>"templates[more]"
		));
		$this->addTextArea("style", array(
			"text" => (isset($temp['style'])) ? $temp['style'] : "",
			"name" => "templates[style]"
		));

		$this->addSelect("label_list", array(
			"name" => "templates[labelId]",
			"options" => $this->getLabelList(),
			"property" => "caption",
			"selected" => (isset($temp["labelId"])) ? $temp["labelId"] : false
		));

		$this->addInput("submit_button", array(
			"value" => (is_null($id)) ? CMSMessageManager::get("SOYCMS_CREATE") : CMSMessageManager::get("SOYCMS_UPDATE")
		));

		$this->addForm("update_form");
	}

	function getLabelList(){
		$res = $this->run("Label.LabelListAction");
		return ($res->success()) ? $res->getAttribute("list") : array();
	}
}
