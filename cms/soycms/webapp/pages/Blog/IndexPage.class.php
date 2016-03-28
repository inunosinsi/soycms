<?php

class IndexPage extends CMSWebPageBase{
	
	var $id;
	var $page;
	protected $labelIds;
		
	function IndexPage($arg){
		
		//最初の値はブログページID
		$id = @$arg[0];
		$this->id = $id;
		
		if(!$id)$this->jump("Blog.List");
		
		WebPage::WebPage();
		
		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($this->id)
		));
		
		$result = $this->run("Blog.DetailAction",array("id"=>$id));
    	if(!$result->success()){
    		$this->addMessage("PAGE_DETAIL_GET_FAILED");
    		$this->jump("Page");
    		exit;
    	}
    	
    	
    	$page = $result->getAttribute("Page");
    	if(!is_null($page->getBlogLabelId())){
    		$this->createAdd("notify_blog_label","HTMLModel",array("visible"=>false));
    		$r = $this->run("Entry.EntryListAction",array(
    				"id"=>$page->getBlogLabelId(),
    				"offset"=>0,
    				"limit"=>1
    			));
    		if($r->getAttribute("total") > 0){
    			$this->createAdd("notify_entry_count","HTMLModel",array("visible"=>false));
    			
    		}else{
    			$this->createAdd("notify_entry_count","HTMLModel",array("visible"=>true));
    			
    		}
    	}else{
    		$this->createAdd("notify_blog_label","HTMLModel",array("visible"=>true));
    		$this->createAdd("notify_entry_count","HTMLModel",array("visible"=>false));
    	}
    	
    	$this->createAdd("blog_config_page_link","HTMLLink",array("link"=>SOY2PageController::createLink("Blog.Config.".$this->id)));
    	$this->createAdd("blog_entry_page_link","HTMLLink",array("link"=>SOY2PageController::createLink("Blog.Entry.".$this->id)));
    	
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
    	
    	$this->createAdd("recent_entry","IndexPage_EntryList",array("list"=>$entries,"pageId"=>$this->id));
    	$this->createAdd("recent_comment","IndexPage_CommentList",array("list"=>$comments,"pageId"=>$this->id));
    	$this->createAdd("recent_trackback","IndexPage_Trackback",array("list"=>$trackbacks,"pageId"=>$this->id));
    	
    	/**
    	 * ToolBox
    	 */
    	CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_DYNAMIC_EDIT"),SOY2PageController::createLink("Page.Preview.".$this->id),false,"this.target = '_blank'");
    	if($page->isActive() == Page::PAGE_ACTIVE && $page->getGenerateTopFlag()){
    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_BLOGPAGE"),UserInfoUtil::getSiteUrl() . $page->getTopPageURL(),false,"this.target = '_blank'");
    	}
    	
    	CMSToolBox::addPageJumpBox();
    	
    	/**
    	 * CSS
    	 */
		HTMLHead::addLink("dashboard",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/blog/dashboard.css")."?".SOYCMS_BUILD_TIME
		));
		
    	
	}
	
	
}


class IndexPage_EntryList extends HTMLList{
	
	var $pageId;
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
	
	function populateItem($entity){
		$this->createAdd("message","HTMLLink",array(
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
		
		$text = date("Y-m-d H:i:s",$entity->getSubmitDate()) . " - ";
		$text .= (strlen($entity->getTitle()) > 0 ) ? $entity->getTitle() : CMSMessageManager::get("SOYCMS_NO_TITLE");
		$text .= ":" . $entity->getBody();
		
		$this->createAdd("message","HTMLLink",array(
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
		$this->createAdd("message","HTMLLink",array(
			"text"=>$entity->getTitle(),
			"link"=>SOY2PageController::createLink("Blog.Trackback.".$this->pageId)
		));
	}
	
}
?>