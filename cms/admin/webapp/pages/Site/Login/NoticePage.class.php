<?php

class NoticePage extends CMSWebPageBase{
	
	function __construct(){
		parent::__construct();
		
		$this->createAdd("candidate_list", "CandidateList", array(
			"list" => SOY2Logic::createInstance("logic.admin.Login.ErrorLogic")->getCandidates()
		));
	}
}

class CandidateList extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLabel("ip", array(
			"text" => $entity->getIp()
		));
		
		$this->addLabel("count", array(
			"text" => $entity->getCount()
		));
		
		$this->addLabel("successed", array(
			"text" => ($entity->getSuccessed() >= 10) ? "(" . $entity->getSuccessed() . ")" : ""
		));
		
		$this->addLabel("start_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getStartDate())
		));
		
		$this->addLabel("update_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getUpdateDate())
		));
		
		$this->addActionLink("measure_link", array(
			"link" => SOY2PageController::createLink("Site.Login.Measure." . $entity->getId())
		));
	}
}
?>