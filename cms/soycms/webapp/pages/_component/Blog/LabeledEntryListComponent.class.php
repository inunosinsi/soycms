<?php

class LabeledEntryListComponent extends HTMLList{

	static $tabIndex = 0;

	private $labelIds;
	private $labelList;
	private $pageId;
	private $labelId;
	private $page;

	private $logic;

	public function setLabelIds($labelIds){
		$this->labelIds = $labelIds;
	}

	public function setLabelList($list){
		$this->labelList = $list;
	}

	public function setPageId($pageId){
		$this->pageId = $pageId;
	}
	public function setLabelId($labelId){
		$this->labelId = $labelId;
	}
	public function setPage($page){
		$this->page = $page;
	}

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addInput("entry_check", array(
			"type"=>"checkbox",
			"name"=>"entry[]",
			"value"=> $id
		));

		$entity->setTitle(strip_tags($entity->getTitle()));
		$title_link = SOY2HTMLFactory::createInstance("HTMLLink",array(
			"text"  => ( (strlen($entity->getTitle())==0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle() ),
			"link"  => SOY2PageController::createLink("Blog.Entry.".$this->pageId.".".$id),
			"title" => $entity->getTitle()
		));

		$this->add("title",$title_link);


		$pageUrl = UserInfoUtil::getSiteUrl() . ( (strlen($this->page->getUri()) >0) ? $this->page->getUri() ."/" : "" ) ;
		$this->addLink("status", array(
			"text" => $entity->getStateMessage(),
			"link" => $pageUrl.$this->page->getEntryPageUri()."/".rawurlencode($entity->getAlias()),
		));

		$this->addLabel("content", array(
			"text"  => mb_strimwidth(SOY2HTML::ToText($entity->getContent()),0,100,"..."),
			"title" => mb_strimwidth(SOY2HTML::ToText($entity->getContent()),0,1000,"..."),
		));

		$this->addLabel("create_date", array(
			"text"  => (is_numeric($entity->getCdate())) ? CMSUtil::getRecentDateTimeText($entity->getCdate()) : "",
			"title" => (is_numeric($entity->getCdate())) ? date("Y-m-d H:i:s",$entity->getCdate()) : "",
		));

		if(!$this->logic) $this->logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		$this->addInput("order", array(
			"type"=>"text",
			"name"=>"displayOrder[".$id."][".$this->labelId."]",
			"value"=> $this->logic->getDisplayOrder($id,$this->labelId),
			"size"=>"5",
			"tabindex" => self::$tabIndex++
		));

		//ラベル表示部
		$this->createAdd("label","_component.Blog.LabelListComponent",array(
			"list" => $this->labelList,
			"entryLabelIds"=>$entity->getLabels(),
			"pageId"=>$this->pageId
		));
	}
}
