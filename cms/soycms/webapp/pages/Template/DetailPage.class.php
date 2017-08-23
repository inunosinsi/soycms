<?php
class DetailPage extends CMSWebPageBase{

	var $id;

	function doPost(){

		$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");

		try{
			$template = $logic->getById($this->id);
		}catch(Exception $e){
			$this->jump("Template");
		}


		if($template->isActive()){
			$this->jump("Template.UnInstall.".$template->getId());
		}else{
			$this->jump("Template.Install.".$template->getId());
		}

	}

	function __construct($args) {
		$id = $args[0];
		$this->id = $id;

		parent::__construct();

		$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");


		try{
			$template = $logic->getById($id);
		}catch(Exception $e){
			$this->jump("Template");
		}

		if($template->isActive()){
			DisplayPlugin::hide("not_installed");
		}else{
			DisplayPlugin::hide("installed");
		}



		$this->createAdd("template_name","HTMLLabel",array(
			"text" => $template->getName()
		));

		$fileList = $template->getFileList();
		$this->createAdd("file_list","FileList",array(
			"list" => $fileList,
			"state"=>$template->isActive()
		));

		if(count($fileList)<1){
			DisplayPlugin::hide("file_list");
		}

		$this->createAdd("template_list","TemplateList",array(
			"list"=>$template->getTemplate(),
			"state"=>$template->isActive(),
			"templateId"=>$template->getId()
		));

		$this->createAdd("operation_link","HTMLLink",array(
			"text"=> ($template->isActive()) ? CMSMessageManager::get("SOYCMS_TEMPLATE_UNINSTALL") : CMSMessageManager::get("SOYCMS_TEMPLATE_INSTALL") ,
			"link"=> ($template->isActive()) ? SOY2PageController::createLink("Template.UnInstall")."/".$this->id."/" : SOY2PageController::createLink("Template.Install")."/".$this->id."/" ,
		));

	}


}

class FileList extends HTMLList{

	private $state;

	function setState($state){
		$this->state = $state;
	}

	function populateItem($entity){

		$path = $entity["path"];

		$flag = false;
		if(file_exists(UserInfoUtil::getSiteDirectory().$path)){
			$flag = true;
		}

		if(@$path[0]=="/") $path = substr($path, 1);

		if($this->state){
			$this->createAdd("name","HTMLLink",array(
				"text" => $path,
				"style" => (!$flag) ? "color:red;cursor:default;text-decoration:line-through;" : "",
				"link"=> UserInfoUtil::getSiteURL().$path,
				"onclick" => (!$flag) ? "return false;" : ""
			));
		}else{
			$this->createAdd("name","HTMLLink",array(
				"text" => $path,
				"style" => ($flag) ? "color:red;" : "cursor:default;",
				"link"=> UserInfoUtil::getSiteURL().$path,
				"onclick" => ($flag) ? "" : "return false;"
			));
		}

		$this->createAdd("description","HTMLLabel",array(
			"text" => $entity["description"]
		));


	}
}

class TemplateList extends HTMLList{

	private $state;
	private $templateId;

	function setState($state){
		$this->state = $state;
	}

	function setTemplateId($id){
		$this->templateId = $id;
	}

	function populateItem($entity){


		$this->createAdd("name","HTMLLabel",array(
			"text"=>$entity["name"]
		));

		$this->createAdd("description","HTMLLabel",array(
			"text"=>$entity["description"]
		));
		$this->createAdd("edit_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Template.Edit")."/".$this->templateId."/".$entity["id"]
		));


	}


}
