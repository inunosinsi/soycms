<?php

class OperateListComponent extends HTMLList{
	
	function populateItem($entity){
		$this->addLabel("operate_credit_title", array(
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));
		
		$this->addLabel("operate_credit_content", array(
			"html" => (isset($entity["content"])) ? $entity["content"] : ""
		));
	}
}