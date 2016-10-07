<?php

class TreePage extends CMSWebPageBase{

	var $treeIcon = "";
	var $trashFlag = false;
	var $trashPage = array();

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


    function __construct() {
    	$result = ($this->run("Page.PageListAction",array("buildtree"=>true)));
    	WebPage::__construct();

    	$this->treeIcon = SOY2PageController::createRelativeLink("./image/tree/tree.gif");

    	$page = $result->getAttribute("PageList");

    	$this->createAdd("page_list","HTMLLabel",array(
    		"html"=> $this->getTreeHTML($page)
    	));

    	$trashPage = $result->getAttribute("RemovedPageList");

    	//ごみ箱を出力
    	if(empty($trashPage)){
    		DisplayPlugin::hide("not_empty_trash");
    	}

    	$this->createAdd("trash_page_list","HTMLLabel",array(
    		"html"=> $this->getTreeHTML($trashPage)
    	));

    }

    function getTreeHTML($pages,$depth = 0){
    	$result = array();
    	$result[] = '<ul>';

    	foreach($pages as $key => $page){

    		$html = array();
    		if($depth>1){
    			$html[] = '<li style="margin-left:35px;margin-top:10px;clear:both;">';
    		}else{
    			$html[] = '<li style="margin-left:15px;margin-top:10px;clear:both;">';
    		}
    		if($depth>0){
	    		$html[] = '<div style="height:40px;float:left;line-height:40px;"><img src="'.$this->treeIcon.'" /></div>';
    		}

			$html[] = '<a href="'.htmlspecialchars(SOY2PageController::createLink("Page.Detail") ."/".$page->getId(), ENT_QUOTES).'" >';
    		$html[] = '<img src="' . $page->getIconUrl() . '" style="float:left;margin-right:5px;" width="40px" height="40px" alt="" />';
    		$html[] = '</a>';

	    	list($iconName,$visible) = $this->getSubIcon($page);
	    	if($visible){
	    		$html[] = '<img style="margin-left:-20px;" width="16" height="16" src="' . $iconName . '" />';
	    	}

	    	if($page->isBlog()){
	    		$html[] = '<img style="margin-left:-30px;" src="' . SOY2PageController::createRelativeLink("./css/pagelist/images/blog.png") . '" />';
	    	}

//	    	$url = UserInfoUtil::getSiteUrl().$page->getUri();
//     		$html[] = '<span ><a style="text-decoration:none;" href="'.$url.'">' . "/" . $page->getUri() . '</a></span>';
//    		$html[] = '<br />';

    		$html[] = $this->buildPageInfo($page);
    		$html[] = '<br />';

    		$function = array();

	     		$function[] = '<a href="' . SOY2PageController::createLink("Page.Detail") . "/" . $page->getId() . '">'.$this->getMessage("SOYCMS_EDIT").'</a>';

		    	$url = CMSUtil::getSiteUrl().$page->getUri();
		    	if(!$visible){
		     		$function[] = '<a href="'.htmlspecialchars(soy2_realurl($url), ENT_QUOTES, "UTF-8").'">'.$this->getMessage("SOYCMS_VIEW").'</a></span>';
		    	}else{
		     		$function[] = '<span style="color:#fB9733;text-decoration:line-through;"><a style="color:#fB9733;text-decoration:underline" href="'.htmlspecialchars(soy2_realurl($url), ENT_QUOTES, "UTF-8").'">'.$this->getMessage("SOYCMS_VIEW").'</a></span>';
		    	}

	     		if($page->getIsTrash()){
	     			$function[] = '<a href="' .SOY2PageController::createLink("Page.Remove")   . "/" . $page->getId(). "?soy2_token=" . soy2_get_token() . '" onclick="return confirm(\''.$this->getMessage("SOYCMS_CONFIRM_DELETE_COMPLETELY").'\');">'.$this->getMessage("SOYCMS_DELETE").'</a>';
	     			$function[] = '<a href="' .SOY2PageController::createLink("Page.Recover")  . "/" . $page->getId(). "?soy2_token=" . soy2_get_token() . '">'.$this->getMessage("SOYCMS_RECOVER").'</a>';
	     		}elseif($page->isDeletable()){
		     		$function[] = '<a href="' .SOY2PageController::createLink("Page.PutTrash") . "/" . $page->getId(). "?soy2_token=" . soy2_get_token() . '">'.$this->getMessage("SOYCMS_DELETE").'</a>';
	     		}

	     		if($page->isCopyable()){
					$function[] = '<a href="'.SOY2PageController::createLink("Page.Copy.".$page->getId())."?soy2_token=" . soy2_get_token() . '">'.$this->getMessage("SOYCMS_COPY").'</a>';
	     		}

				$function[] = '<a href="'.SOY2PageController::createLink("Page.Preview.".$page->getId()).'" target="_blank">'.$this->getMessage("SOYCMS_DYNAMIC_EDIT").'</a>';

     		$html[] = implode("&nbsp;&nbsp;", $function);

    		if(count($page->getChildPages()) != 0){
    			$html[] = $this->getTreeHTML($page->getChildPages(),$depth+1);
    		}
    		$html[] = '</li>';
    		$result[] = implode("\n".str_repeat("  ",$depth),$html);
    	}
    	$result[] = '</ul>';

    	return implode("\n",$result);
    }

    /**
     * Treeのページ１個あたりの情報を表示
     */
    function buildPageInfo($page){
    	return '<a class="soy_page_name" href="'.htmlspecialchars(SOY2PageController::createLink("Page.Detail") ."/".$page->getId(), ENT_QUOTES).'" title="'.htmlspecialchars($page->getTitle(), ENT_QUOTES, "UTF-8").'">'.htmlspecialchars($this->trimPageTitle($page->getTitle()), ENT_QUOTES, "UTF-8").'</a>';
    }

    function trimPageTitle($title){
		$str = mb_strimwidth($title,0,100,"...");

		return $str;
	}
}

class TreeListComponent extends HTMLList{

	function populateItem($entity){}
}
?>