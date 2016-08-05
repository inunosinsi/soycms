<?php

class ListPage extends CMSWebPageBase{

    function __construct() {
    	$result = $this->run("Blog.BlogListAction");
    	
    	$list = $result->getAttribute("list");
    	
    	WebPage::WebPage();
    	
    	$this->createAdd("page_list","BlogPageList",array(
    		"list"=>$list
    	));
    	
    	if(count($list)>0){
    		//do nothing
    		$this->createAdd("exists_blog_page","HTMLModel",array(
    			"visible"=>true
    		));
    		$this->createAdd("no_blog_message","HTMLModel",array(
    			"visible"=>false
    		));
    	}else{
    		$this->createAdd("exists_blog_page","HTMLModel",array(
    			"visible"=>false
    		));
    		$this->createAdd("no_blog_message","HTMLModel",array(
    			"visible"=>true
    		));
    		$this->createAdd("link_to_create_blog","HTMLModel",array(
    			"visible"=>UserInfoUtil::hasSiteAdminRole()
    		));
    	}
    	
    	HTMLHead::addLink("page_list",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/pagelist/pagelist.css")
		));

    }
}

class BlogPageList extends HTMLList{
	var $blogIcon;
	var $deletedIcon;
	var $notopenIcon;
	var $draftIcon;
	var $grayIcon;
	var $greenIcon;
		
	function getDeletedIcon(){
		if(!$this->deletedIcon){
			$this->deletedIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/cross.png");
		}
		
		return $this->deletedIcon;
	}
	
	function getDraftIcon(){
		if(!$this->draftIcon){
			$this->draftIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/draft.gif");
		}
		
		return $this->draftIcon;
	}
	
	function getGrayIcon(){
		if(!$this->grayIcon){
			$this->grayIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/after.gif");
		}
		
		return $this->grayIcon;
	}
	
	function getGreenIcon(){
		if(!$this->greenIcon){
			$this->greenIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/before.gif");
		}
		
		return $this->greenIcon;
	}
	
	
	
	function getSubIcon($page){
		$visible = $page->getPageType() != Page::PAGE_TYPE_ERROR;
		$src = "";
		
		if($page->getIsTrash()){
			$src = $this->getDeletedIcon();	
		}else{
			switch($page->isActive(true)){
				case Page::PAGE_ACTIVE:
				case Page::PAGE_ACTIVE_CLOSE_BEFORE:
					$visible = false;
					$src = "";
					break;
				case Page::PAGE_ACTIVE_CLOSE_FUTURE:
					$src = $this->getGreenIcon();
					break;
				case Page::PAGE_OUTOFDATE_BEFORE:
					$src = $this->getGrayIcon();
					break;
				case Page::PAGE_OUTOFDATE_PAST:
				case Page::PAGE_NOTPUBLIC:
					$src = $this->getDraftIcon();
					break;
			}
		}
		
		
		return array($src,$visible);
	}
	
	function getBlogIcon(){
		if(!$this->blogIcon){
			$this->blogIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/blog.png");
		}
		
		return $this->blogIcon;
	}	
	
	function populateItem($entity){
		$pageType = (int)$entity->getPageType();

   		$pageUrl = (strlen($entity->getUri()) >0) ? "/{$entity->getUri()}/" : "/" ;
   		$pageFullUrl = CMSUtil::getSiteUrl() . ( (strlen($entity->getUri()) >0) ? $entity->getUri() ."/" : "" ) ;
		
		$this->createAdd("page_icon","HTMLModel",array(
			"style" => "background-image:url('".$entity->getIconUrl()."')"
		));
		
		$this->createAdd("title","HTMLLink",array(
			"text"=> mb_strimwidth($entity->getTitle(),0,44,"..."),
			"link"=>SOY2PageController::createLink("Blog") ."/".$entity->getId()
		));
		
		$this->createAdd("page_url","HTMLLink",array(
			"text" => $pageUrl,
			"link" => $pageFullUrl,
		));
		$this->createAdd("uri","HTMLLabel",array("text"=>$entity->getUri()));
		$this->createAdd("update_date","HTMLLabel",array("text"=>date('Y-m-d',$entity->getUdate())));
		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog") ."/".$entity->getId()
		));
		$this->createAdd("entry_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.EntryList") ."/".$entity->getId()
		));
		
		$this->createAdd("post_entry_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.Entry") ."/".$entity->getId()
		));
				
		$this->createAdd("config_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.Config") ."/".$entity->getId(),
			 "visible" => UserInfoUtil::hasSiteAdminRole()
		));
		$this->createAdd("template_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.Template") ."/".$entity->getId(),
			"visible" => (UserInfoUtil::hasSiteAdminRole())
		));

		$this->createAdd("trackback_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.Trackback") ."/".$entity->getId()
		));
		$this->createAdd("comment_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Blog.Comment") ."/".$entity->getId()
		));
		
		$trashLink = "";
		if($entity->getIsTrash() == 1){
			$trashLink = SOY2PageController::createLink("Page.Remove") . "/" . $entity->getId();
			$onclick= "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_DELETE_COMPLETELY")."');";
		}else{
			$trashLink = SOY2PageController::createLink("Page.PutTrash") . "/" . $entity->getId();
			$onclick = "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_MOVE_INTO_TRASHBOX")."');";
		}
		$this->createAdd("delete_link","HTMLActionLink",array(
			"link" => $trashLink,
			"onclick" => $onclick,
			"visible" => $entity->isDeletable() && UserInfoUtil::hasSiteAdminRole()
		));
		
		$this->createAdd("recover_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.Recover") . "/" . $entity->getId(),
			'onclick' => "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_RECOVER_WEBPAGE")."');",
			"visible" => $entity->getIsTrash() && UserInfoUtil::hasSiteAdminRole()
		));
		
		$this->createAdd("preview_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Page.Preview.".$entity->getId())
		));
		
		$this->createAdd("client_view","HTMLLink",array(
			"link"=>$pageFullUrl,
			"text"=>CMSMessageManager::get("SOYCMS_CONFIRM"),
			"visible"=>$entity->isActive()>0
		));	
		
		//公開してなかったら×を表示
		list($src,$visible) = $this->getSubIcon($entity);
		$this->createAdd("is_deleted","HTMLImage",array(
			"src" => $src,
			"visible" => $visible
		));
		
		
	}
	
}
?>