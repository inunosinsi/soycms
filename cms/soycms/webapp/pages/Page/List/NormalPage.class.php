<?php

class NormalPage extends CMSWebPageBase{

    function __construct() {
    	WebPage::__construct();
    	
    	$offset = 0; $viewcount = 10; $vieworder = "udate";
    	extract($this->getViewParam());//$offset, $viewcount, $vieworder
    	
    	$pages = $this->getPageList($offset,$viewcount,$vieworder);
    	$total = $this->getTotalPageCount();

    	$this->createAdd("page_list","PageList",array(
    		"list" => $pages
    	));
    	
    	$this->createAdd("pager_form","HTMLForm",array(
    		"method"=>"GET"
    	));
    	
    	$this->createAdd("total","HTMLLabel",array(
    		"text"=>$total
    	));
    	
    	
    	
    	$this->createAdd("pager_start","HTMLLabel",array(
    		"text"=>$offset+1
    	));
    	
    	$this->createAdd("pager_end","HTMLLabel",array(
    		"text"=>($offset+count($pages))
    	));
    	
    	$this->createAdd("viewcount","HTMLSelect",array(
    		"options"=>array(
    			"10"=>$this->getMessage("SOYCMS_10_ITEMS"),
    			"20"=>$this->getMessage("SOYCMS_20_ITEMS"),
    			"30"=>$this->getMessage("SOYCMS_30_ITEMS")
    		),
    		"name"=>"viewcount",
    		"indexOrder"=>true,
    		"selected"=>$viewcount
    	));
    	
    	$this->createAdd("vieworder","HTMLSelect",array(
    		"options"=>array(
    			"type" =>$this->getMessage("SOYCMS_SORT_DIFFERENCE"),
    			"udate"=>$this->getMessage("SOYCMS_RELOAD_ORDER"),
    			"id"=>$this->getMessage("SOYCMS_CREATE_ORDER")
    		),
    		"name"=>"vieworder",
    		"indexOrder"=>true,
    		"selected"=>$vieworder
    	));
    	
    	
    	
		$this->add("pageTreeMode",SOY2HTMLFactory::createInstance("HTMLLabel",array(
			"text" =>$this->getMessage("SOYCMS_INDICATION_CHANGE")
		)));
		
		HTMLHead::addLink("page_list",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/pagelist/pagelist.css")."?".SOYCMS_BUILD_TIME
		));
		
		$this->createAdd("pager_prev","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Page")."?offset=".($offset-$viewcount)."&viewcount=".$viewcount."&vieworder=".$vieworder,
			"visible"=>($offset > 0)
		));
		
		$this->createAdd("pager_next","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Page")."?offset=".($offset+$viewcount)."&viewcount=".$viewcount."&vieworder=".$vieworder,
			"visible"=>($offset+$viewcount <= $total)
		));
		
		$str = array();
		for($i = 0; $i<ceil($total/$viewcount); $i++){
			if($i*$viewcount == $offset){
				$str[] = '<span>'.($i+1)."</span>";
			}else{
				$str[] = '<a href="'.SOY2PageController::createLink("Page").'?offset='.($i*$viewcount)."&viewcount=".$viewcount."&vieworder=".$vieworder.'">'.($i+1)."</a>";
			}
		}
		
		$this->createAdd("pager_list","HTMLLabel",array(
			"html"=>implode("&nbsp;",$str)
		));
		
    }
    
    /**
     * ページ一覧を返す
     * @return array(ページのツリー、ゴミ箱ツリー)
     */
    function getPageList($offset,$count,$order){
    	
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset"=>$offset,
    		"count"=>$count,
    		"order"=>$order
    	))->run();
    	
    	$list = $result->getAttribute("PageList") + $result->getAttribute("RemovedPageList");
    	
    	return $list;       			    	
    }
    
    function getTotalPageCount(){
    	$result = $this->run("Page.PageCountAction");
    	return $result->getAttribute("PageCount");
    }

    function updateCookie($cookieName, $hash){
		$time = time() + 3*30*24*60*60;
		foreach($hash as $name => $value){
			@setcookie($cookieName[$name],$value,$time);
		}
	}
	
	function getViewParam(){
    	$viewParam = array(
    		"offset" => ( isset($_GET["offset"])? $_GET["offset"] : 0 ),
    		"viewcount" => ( isset($_GET["viewcount"]) ? $_GET["viewcount"] : 10 ),
    		"vieworder" => ( isset($_GET["vieworder"]) ? $_GET["vieworder"] : "udate" )
    	);

    	if(count(array_intersect_key($viewParam, $_GET)) >0){
    		$this->updateCookie("Page_ViewParam", $viewParam);
    		return $viewParam;
    	}elseif(isset($_COOKIE["Page_ViewParam"])){
			return $_COOKIE["Page_ViewParam"];
    	}else{
    		return $viewParam;
    	}
	}

}

class PageList extends HTMlList{
	
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
		
		$this->createAdd("page_icon","HTMLModel",array(
			"style" => "background-image:url('".$entity->getIconUrl()."')"
		));
		$this->createAdd("page_icon_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.Detail") ."/".$entity->getId(),
		));
		
		$this->createAdd("title","HTMLLink",array(
			"text" => $entity->getTitle(),
			"link" => SOY2PageController::createLink("Page.Detail") ."/".$entity->getId(),
			"title"=> $entity->getTitle(),
			"width"=> 40
		));
		
		$this->createAdd("uri","HTMLLink",array(
			"text" => "/".$entity->getUri(),
			"link"=>CMSUtil::getSiteUrl().$entity->getUri(),
		));
		
		$this->createAdd("update_date","HTMLLabel",array(
			"text" => date("Y-m-d",$entity->getUdate())
		));
		
		
		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.Detail") ."/".$entity->getId()
		));
		
		$trashLink = "";
		if($entity->getIsTrash() == 1){
			$trashLink = SOY2PageController::createLink("Page.Remove") . "/" . $entity->getId();
			$onclick= "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_DELETE_COMPLETELY")."');";
		}else{
			$trashLink = SOY2PageController::createLink("Page.PutTrash") . "/" . $entity->getId();
			$onclick = "";
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
		
		$this->createAdd("preview_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Page.Preview.".$entity->getId())
		));
		
		$this->createAdd("copy_link","HTMLActionLink",array(
			"link"=>SOY2PageController::createLink("Page.Copy.".$entity->getId()),
			"visible" => ($entity->isCopyable())
		));
		
		$this->createAdd("client_view","HTMLLink",array(
			"link"=>CMSUtil::getSiteUrl().$entity->getUri(),
			"html" => ($entity->isActive()>0) ? CMSMessageManager::get("SOYCMS_VIEW") : "<s>".CMSMessageManager::get("SOYCMS_VIEW")."</s>" 
		));		
		
		//公開してなかったら×を表示
		list($src,$visible) = $this->getSubIcon($entity);
		$this->createAdd("is_deleted","HTMLImage",array(
			"src" => $src,
			"visible" => $visible
		));
		
		//ブログだったらブログを表示
		$this->createAdd("is_blog","HTMLImage",array(
			"src" => $this->getBlogIcon(),
			"visible" => $entity->isBlog()
		));
		
		
		$this->createAdd("page_panel","HTMLModel",array(
			"visible" => true
		));
		
	}
	
}
?>