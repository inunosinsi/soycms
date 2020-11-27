<?php

class IndexPage extends CMSWebPageBase{

	function __construct($arg){
		//最初の値はブログページID
		if(!is_array($arg) || !count($arg) || !strlen($arg[0])){
			$this->jump('Blog.List');
		}

		$id = $arg[0];

		//公開権限がない場合は記事一覧へ
		if(!UserInfoUtil::hasEntryPublisherRole()){
			$this->jump("Blog.EntryList.".$id);
		}

		parent::__construct();


		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($id)
		));

		$result = $this->run("Blog.DetailAction",array("id"=>$id));
		if(!$result->success()){
			$this->addMessage("PAGE_DETAIL_GET_FAILED");
			$this->jump("Page");
			exit;
		}
		$page = $result->getAttribute("Page");


		$this->createAdd("blog_config_page_link","HTMLLink",array("link"=>SOY2PageController::createLink("Blog.Config.".$id)));
		$this->createAdd("blog_entry_page_link","HTMLLink",array("link"=>SOY2PageController::createLink("Blog.Entry.".$id)));

		$labelId = $page->getBlogLabelId();

		$entryResult = $this->run("Entry.RecentEntryListByLabelId",array("labelId"=>$labelId));
		$entries = $entryResult->getAttribute("entries");

		$commentResult = $this->run("EntryComment.RecentCommentListAction",array("labelId"=>$labelId));
		$comments = $commentResult->getAttribute("comments");

		$trackbackResult = $this->run("EntryTrackback.RecentTrackbackListAction",array("labelId"=>$labelId));
		$trackbacks = $trackbackResult->getAttribute("trackbacks");

		$this->createAdd("empty_entry_msg","HTMLModel",array("visible"=>count($entries) == 0));
		$this->createAdd("empty_comment_msg","HTMLModel",array("visible"=>count($comments) == 0));
		$this->createAdd("empty_trackback_msg","HTMLModel",array("visible"=>count($trackbacks) == 0));

		$this->createAdd("has_entry_msg","HTMLModel",array("visible"=>count($entries) > 0));
		$this->createAdd("has_comment_msg","HTMLModel",array("visible"=>count($comments) > 0));
		$this->createAdd("has_trackback_msg","HTMLModel",array("visible"=>count($trackbacks) > 0));

		$this->createAdd("recent_entry","IndexPage_EntryList",array("list"=>$entries,"pageId"=>$id));
		$this->createAdd("recent_comment","IndexPage_CommentList",array("list"=>$comments,"pageId"=>$id));
		$this->createAdd("recent_trackback","IndexPage_Trackback",array("list"=>$trackbacks,"pageId"=>$id));

		/**
		 * ToolBox
		 */
		CMSToolBox::addPageJumpBox();

	}

}


class IndexPage_EntryList extends HTMLList{

	var $pageId;

	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	function populateItem($entity){
		$this->addLink("message", array(
			"text" => (strlen($entity->getTitle())==0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
			"link"=>SOY2PageController::createLink("Blog.Entry.".$this->pageId.".".$entity->getId())
		));
	}

}

class IndexPage_CommentList extends HTMLList{

	var $pageId;

	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	function populateItem($entity){

		$text = (is_numeric($entity->getSubmitDate())) ? date("Y-m-d H:i:s", $entity->getSubmitDate()) . " - " : "";
		$text .= (strlen($entity->getTitle()) > 0 ) ? $entity->getTitle() : CMSMessageManager::get("SOYCMS_NO_TITLE");
		$text .= ":" . $entity->getBody();

		$this->addLink("message", array(
			"text"=> $text,
			"link"=>SOY2PageController::createLink("Blog.Comment.".$this->pageId)
		));
	}

}

class IndexPage_Trackback extends HTMLList{

	var $pageId;

	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	function populateItem($entity){
		$this->addLink("message", array(
			"text"=>$entity->getTitle(),
			"link"=>SOY2PageController::createLink("Blog.Trackback.".$this->pageId)
		));
	}

}
