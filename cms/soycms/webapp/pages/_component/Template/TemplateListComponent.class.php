<?php

class TemplateListComponent extends HTMLList{

	private function getLink($template){
		if($template->isActive()){
			return SOY2PageController::createLink("Template.Detail");
		}else{
			return SOY2PageController::createLink("Template.Detail");
		}
	}

	public function populateItem($entity){
		$this->addLink("title", array(
			"text"=>(strlen($entity->getName()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") :$entity->getName(),
			"link"=>$this->getLink($entity).'/'.$entity->getId()
		));
		$this->addLabel("template_type", array(
			"text"=>($entity->getPageType() == Page::PAGE_TYPE_NORMAL)? CMSMessageManager::get("SOYCMS_TEMPLATE_FOR_NORMALPAGE") : CMSMessageManager::get("SOYCMS_TEMPLATE_FOR_BLOGPAGE")
		));

		$this->addLabel("description", array(
			"text"=>strip_tags($entity->getDescription())
		));

		$this->addActionLink("install_link", array(
			"link"=> SOY2PageController::createLink("Template.Install").'/'.$entity->getId(),
			"visible" => (!$entity->isActive())
		));

		$this->addActionLink("uninstall_link", array(
			"link"=> SOY2PageController::createLink("Template.UnInstall").'/'.$entity->getId(),
			"visible" => ($entity->isActive())
		));

		$this->addLink("modify_link", array(
			"link" => $this->getLink($entity).'/'.$entity->getId(),
			"text" => ($entity->isActive()) ? CMSMessageManager::get("SOYCMS_EDIT") : CMSMessageManager::get("SOYCMS_DETAIL")
		));
		$this->addActionLink("remove_link", array(
			"link"=>SOY2PageController::createLink("Template.Remove").'/'.$entity->getId(),
			"visible" => (!$entity->isActive())
		));

		$this->addActionLink("download", array(
			"link"=>SOY2PageController::createLink("Template.Download").'/'.$entity->getId(),
			"target"=>"download_frame"
		));
	}
}