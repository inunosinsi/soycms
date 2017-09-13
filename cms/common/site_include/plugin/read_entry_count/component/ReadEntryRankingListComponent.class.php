<?php

class ReadEntryRankingListComponent extends HTMLList {

	private $blogs;
	private $prefix = "cms";

	protected function populateItem($entity){
		$url = self::getBlogPageUrl($entity["labels"]);

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

	private function getBlogPageUrl($labelIds){
		if(!count($this->blogs)) return "";
		foreach($this->blogs as $labelId => $url){
			if(in_array($labelId, $labelIds)) return $url;
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
