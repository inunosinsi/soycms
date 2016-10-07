<?php

class TablePage extends CMSWebPageBase{

    function __construct() {
    	WebPage::__construct();

    	$pages = $this->getPageList();

    	$this->createAdd("page_list","PageList",array(
    		"list" => $pages
    	));

    	HTMLHead::addLink("pagetable.css",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/pagetable/pagetable.css")."?".SOYCMS_BUILD_TIME
		));
    }

    function getPageList(){

    	$result = SOY2ActionFactory::createInstance("Page.PageListAction")->run();

    	$list = $result->getAttribute("PageList") + $result->getAttribute("RemovedPageList");

    	return $list;
    }
}

class PageList extends HTMlList{

	var $blogIcon;
	var $deletedIcon;
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

		$this->createAdd("page_icon","HTMLImage",array(
			"src" => $entity->getIconUrl(),
			"alt" => ""
		));
		$this->createAdd("page_icon_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.Detail") ."/".$entity->getId(),
		));

		$this->createAdd("title","HTMLLink",array(
			"text" => $entity->getTitle(),
			"link" => SOY2PageController::createLink("Page.Detail") ."/".$entity->getId(),
			"title"=> $entity->getTitle(),
		));

		$this->createAdd("uri","HTMLLabel",array(
			"text" => $entity->getUri()
		));

		$this->createAdd("update_date","HTMLLabel",array(
			"text" => date("Y-m-d",$entity->getUdate())
		));

		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.Detail") ."/".$entity->getId()
		));

		$trashLink = $onclick = "";
		if($entity->getIsTrash() == 1){
			$trashLink = SOY2PageController::createLink("Page.Remove") . "/" . $entity->getId();
			$onclick= "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_DELETE_COMPLETELY")."');";
		}elseif($entity->isDeletable()){
			$trashLink = SOY2PageController::createLink("Page.PutTrash") . "/" . $entity->getId();
			$onclick = "";
		}else{
			$onclick = "return false";
		}

		$this->createAdd("delete_link","HTMLActionLink",array(
			"link" => $trashLink,
			"onclick" => $onclick,
			"visible" => ($entity->isDeletable())
		));

		$this->createAdd("recover_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink("Page.Recover") . "/" . $entity->getId(),
			"visible" => $entity->getIsTrash()
		));
		
		$this->createAdd("copy_link","HTMLActionLink",array(
			"link"=>SOY2PageController::createLink("Page.Copy.".$entity->getId()),
			"visible" => ($entity->isCopyable())
		));

		$this->createAdd("preview_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Page.Preview.".$entity->getId())
		));

		//削除されていたら×画像を表示
		//公開してなかったら×を表示
		list($src,$visible) = $this->getSubIcon($entity);
		$this->createAdd("is_deleted","HTMLImage",array(
			"src" => $src,
			"visible" => $visible,
			"width"=>"16",
			"height"=>"16"
		));

		$this->createAdd("client_view","HTMLLink",array(
			"link"    => soy2_realurl(CMSUtil::getSiteUrl().$entity->getUri()),
			"style"   => ( !$visible ? "" : "color:#fB9733;text-decoration:line-through;" )
		));

		//ブログだったらブログを表示
		$this->createAdd("is_blog","HTMLImage",array(
			"src" => $this->getBlogIcon(),
			"visible" => $entity->isBlog() && $entity->getIsTrash()
		));

		$this->createAdd("page_panel","HTMLModel",array(
			"visible" => !false//$entity->isBlog()
		));

		$this->createAdd("state","HTMLLabel",array(
			"text"=>$entity->getStateMessage()
		));

	}

}
?>