<?php
SOY2DAOFactory::importEntity("cms.Page");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		$result = SOY2ActionFactory::createInstance("Template.TemplateListAction")->run();
		$list = $result->getAttribute("list");

		$this->createAdd("no_template_message","HTMLModel",array(
			"visible"=>count($list)==0
		));
		if(count($list) == 0){
			DisplayPlugin::hide("must_exists_template");
		}
		$this->createAdd("template_list","TemplateList",array("list"=>$list));
	}

}

class TemplateList extends HTMLList{

	private function getLink($template){
		if($template->isActive()){
			return SOY2PageController::createLink("Template.Detail");
		}else{
			return SOY2PageController::createLink("Template.Detail");
		}
	}

	public function populateItem($entity){
		$this->createAdd("title","HTMLLink",array(
			"text"=>(strlen($entity->getName()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") :$entity->getName(),
			"link"=>$this->getLink($entity).'/'.$entity->getId()
		));
		$this->createAdd("template_type","HTMLLabel",array(
			"text"=>($entity->getPageType() == Page::PAGE_TYPE_NORMAL)? CMSMessageManager::get("SOYCMS_TEMPLATE_FOR_NORMALPAGE") : CMSMessageManager::get("SOYCMS_TEMPLATE_FOR_BLOGPAGE")
		));

		$this->createAdd("description","HTMLLabel",array(
			"text"=>strip_tags($entity->getDescription())
		));

		$this->createAdd("install_link","HTMLActionLink",array(
			"link"=> SOY2PageController::createLink("Template.Install").'/'.$entity->getId(),
			"visible" => (!$entity->isActive())
		));

		$this->createAdd("uninstall_link","HTMLActionLink",array(
			"link"=> SOY2PageController::createLink("Template.UnInstall").'/'.$entity->getId(),
			"visible" => ($entity->isActive())
		));

		$this->createAdd("modify_link","HTMLLink",array(
			"link" => $this->getLink($entity).'/'.$entity->getId(),
			"text" => ($entity->isActive()) ? CMSMessageManager::get("SOYCMS_EDIT") : CMSMessageManager::get("SOYCMS_DETAIL")
		));
		$this->createAdd("remove_link","HTMLActionLink",array(
			"link"=>SOY2PageController::createLink("Template.Remove").'/'.$entity->getId(),
			"visible" => (!$entity->isActive())
		));

		$this->createAdd("download","HTMLActionLink",array(
			"link"=>SOY2PageController::createLink("Template.Download").'/'.$entity->getId(),
			"target"=>"download_frame"
		));
	}
}

