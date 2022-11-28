<?php

class RecordListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLink("page_url", array(
			"link" => $entity->getUrl(),
			"text" => $entity->getUrl(),
			"target" => "_blank"
		));
		
		$this->addLink("http_referer", array(
			"link" => $entity->getReferer(),
			"text" => $entity->getReferer(),
			"target" => "_blank"
		));
		
		$this->addLabel("register_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getRegisterDate())
		));
		
	}
}
?>