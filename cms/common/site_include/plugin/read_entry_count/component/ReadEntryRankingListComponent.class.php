<?php

class ReadEntryRankingListComponent extends HTMLList {

	private $blogs;
	private $prefix = "cms";

	protected function populateItem($entity){
		$url = (isset($entity["labels"]) && is_array($entity["labels"]) && count($entity["labels"])) ? self::_getBlogPageUrl($entity["labels"]) : "";

		$this->addLink("entry_link", array(
			"soy2prefix" => $this->prefix,
			"link" => (isset($entity["alias"])) ? $url . $entity["alias"] : null
		));

		$this->addLabel("title", array(
			"soy2prefix" => $this->prefix,
			"text" => (isset($entity["title"])) ? $entity["title"] : null
		));

		$this->addLabel("count", array(
			"soy2prefix" => $this->prefix,
			"text" => (isset($entity["count"])) ? (int)$entity["count"] : 0
		));

		if(isset($entity["id"]) && is_numeric($entity["id"])){
			$entry = $this->entryDao->getObject($entity);
		}else{
			$entry = new Entry();
		}

		//カスタムフィールドを使用できるように
		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entry->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entry));

		if(!isset($entity["labels"]) || !count($entity["labels"]) || !strlen($url)) return false;
	}

	private function _getBlogPageUrl($labelIds){
		if(!count($this->blogs) || !is_array($labelIds) || !count($labelIds)) return "";
		foreach($this->blogs as $labelId => $url){
			if(is_numeric(array_search($labelId, $labelIds))) return $url;
		}
		return "";
	}

	function setBlogs($blogs){
		$this->blogs = $blogs;
	}

	function setEntryDao($entryDao){
		$this->entryDao = $entryDao;
	}
}
