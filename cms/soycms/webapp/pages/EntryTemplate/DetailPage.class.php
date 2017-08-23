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

		$id = @$arg[0];
		$this->id =$id;
		parent::__construct();

		$this->createAdd("page_title","HTMLLabel",array(
				"text" => ($this->id) ? CMSMessageManager::get("SOYCMS_ENTRY_TEMPLATE_DETAIL") : CMSMessageManager::get("SOYCMS_CREATE_NEW")
		));


		$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateDetailAction",array("id"=>$id))->run();
		$template = $result->getAttribute("entity");
		$this->createAdd("template_id","HTMLInput",array(
			"value"=>$template->getId(),
			"name"=>"template_id"
		));
		$this->createAdd("name","HTMLInput",array(
			"value"=>$template->getName(),
			"name"=>"name"
		));

		$this->createAdd("description","HTMLTextArea",array(
			"text"=>$template->getDescription(),
			"name"=>"description"
		));
		$temp = $template->getTemplates();
		$this->createAdd("content","HTMLTextArea",array(
			"text"=>$temp['content'],
			"name"=>"templates[content]"
		));
		$this->createAdd("more","HTMLTextArea",array(
			"text"=>$temp['more'],
			"name"=>"templates[more]"
		));
		$this->createAdd("style","HTMLTextArea",array(
			"text"=>$temp['style'],
			"name"=>"templates[style]"
		));

		$this->createAdd("label_list","HTMLSelect",array(
			"name" => "templates[labelId]",
			"options" => $this->getLabelList(),
			"property" => "caption",
			"selected" => @$temp["labelId"]
		));

		$this->createAdd("submit_button","HTMLInput",array(
			"value" => (strlen($id)<1) ? CMSMessageManager::get("SOYCMS_CREATE") : CMSMessageManager::get("SOYCMS_UPDATE")
		));

		$this->createAdd("update_form","HTMLForm");
	}

	function getLabelList(){
		$res = $this->run("Label.LabelListAction");
		if($res->success()){
			return $res->getAttribute("list");
		}

		return array();
	}
}
