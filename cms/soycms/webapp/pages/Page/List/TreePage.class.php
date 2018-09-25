<?php

class TreePage extends CMSWebPageBase{

	private $treeIcon = "";
	private $trashFlag = false;
	private $trashPage = array();

	private function getDeletedIcon(){
		static $icon;
		if(is_null($icon)) $icon = SOY2PageController::createRelativeLink("./css/pagelist/images/cross.png");
		return $icon;
	}

	private function getDraftIcon(){
		static $icon;
		if(is_null($icon)) $icon = SOY2PageController::createRelativeLink("./css/pagelist/images/draft.gif");
		return $icon;
	}

	private function getGrayIcon(){
		static $icon;
		if(is_null($icon)) $icon = SOY2PageController::createRelativeLink("./css/pagelist/images/after.gif");
		return $icon;
	}

	private function getGreenIcon(){
		static $icon;
		if(is_null($icon)) $icon = SOY2PageController::createRelativeLink("./css/pagelist/images/before.gif");
		return $icon;
	}



	private function getSubIconPath($page){
		if($page->getPageType() == Page::PAGE_TYPE_ERROR){
			return "";
		}elseif($page->getIsTrash()){
			return self::getDeletedIcon();
		}else{
			switch($page->isActive(true)){
				case Page::PAGE_ACTIVE_CLOSE_FUTURE:
					return self::getGreenIcon();
				case Page::PAGE_OUTOFDATE_BEFORE:
					return self::getGrayIcon();
				case Page::PAGE_OUTOFDATE_PAST:
				case Page::PAGE_NOTPUBLIC:
					return self::getDraftIcon();
				case Page::PAGE_ACTIVE:
				case Page::PAGE_ACTIVE_CLOSE_BEFORE:
				default:
					return "";
			}
		}
	}

	private function getBlogIcon(){
		static $icon;
		if(is_null($icon)) $icon = SOY2PageController::createRelativeLink("./css/pagelist/images/blog.gif");
		return $icon;
	}


	public function __construct() {
		$result = ($this->run("Page.PageListAction",array("buildtree"=>true)));
		parent::__construct();

		$this->treeIcon = SOY2PageController::createRelativeLink("./image/tree/tree.gif");

		$page = $result->getAttribute("PageList");

		$this->addLabel("page_list", array(
			"html"=> self::getTreeHTML($page)
		));

		$trashPage = $result->getAttribute("RemovedPageList");

		//ごみ箱を出力
		if(empty($trashPage)){
			DisplayPlugin::hide("not_empty_trash");
		}

		$this->addLabel("trash_page_list", array(
			"html"=> $this->getTreeHTML($trashPage)
		));

		//ページテンプレート管理
		DisplayPlugin::toggle("is_page_template_enabled", CMSUtil::isPageTemplateEnabled());

		$this->addCheckBox("hide-draft",array(
			"selected" => isset($_COOKIE['page-index-hide-draft']) && $_COOKIE['page-index-hide-draft'] == 'true',
			"elementId" => 'hide-draft',
		));
		$this->addCheckBox("hide-out-of-publishing-period",array(
			"selected" => isset($_COOKIE['page-index-hide-out-of-publishing-period']) && $_COOKIE['page-index-hide-out-of-publishing-period'] == 'true',
			"elementId" => 'hide-out-of-publishing-period',
		));
	}

	private function getTreeHTML($pages,$depth = 0){
		$result = array();
		$result[] = '<ul class="list-unstyled">';//style="list-style-type: none">';

		foreach($pages as $key => $page){

			$class_for_branch = array();
			if($depth > 1){
				$class_for_branch[] = "soycms-page-tree-lower-page";
			}

			$class_for_leaf = array();
			if($page->isBlog()){
				$class_for_leaf[] = "blog_page";
			}
			if($page->isMobile()){
				$class_for_leaf[] = "mobile_page";
			}
			if($page->getIsTrash()){
				$class_for_leaf[] = "in_trash";
			}

			if($page->isActive() == Page::PAGE_NOTPUBLIC){
				$class_for_leaf[] = "draft";
				if(isset($_COOKIE['page-index-hide-draft']) && $_COOKIE['page-index-hide-draft'] == 'true'){
					$class_for_leaf[] = "collapse";
				}else{
					$class_for_leaf[] = "collapse in";
				}
			}else{
				if($page->isActive() == Page::PAGE_OUTOFDATE){
					$class_for_leaf[] = "out_of_open_period";
					if(isset($_COOKIE['page-index-hide-out-of-publishing-period']) && $_COOKIE['page-index-hide-out-of-publishing-period'] == 'true'){
						$class_for_leaf[] = "collapse";
					}else{
						$class_for_leaf[] = "collapse in";
					}
				}else{
					$class_for_leaf[] = "currently_published";
				}
			}

			$html = array();
			$html[] = '<li class="'.htmlspecialchars(implode(" ", $class_for_branch), ENT_QUOTES, SOY2HTML::ENCODING).'">' ;
			$html[] = '<div class="'.htmlspecialchars(implode(" ", $class_for_leaf), ENT_QUOTES, SOY2HTML::ENCODING).'">';
			$html[] = self::buildPageIcon($page, $depth);
			$html[] = self::buildPageTitle($page);
			$url = CMSUtil::getSiteUrl().$page->getUri();
			$html[] = '<a href="'.htmlspecialchars($url, ENT_QUOTES, SOY2HTML::ENCODING).'" target="_blank">'.htmlspecialchars($page->getUri(), ENT_QUOTES, SOY2HTML::ENCODING).'<i class="fa fa-external-link fa-fw" style="font-size:smaller"></i></a>';
			$html[] = '<br>';
			$html[] = self::buildPageMenu($page);
			$html[] = '</div>';
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
	 * ページアイコンとツリー表示
	 * @param unknown $page
	 * @param unknown $depth
	 * @return unknown
	 */
	private function buildPageIcon($page, $depth){
		$html = array();

		if($depth>0){
			$html[] = '<img class="soycms-page-tree-branch" src="'.htmlspecialchars($this->treeIcon, ENT_QUOTES, SOY2HTML::ENCODING).'">';
		}

		$html[] = '<a href="'.htmlspecialchars(SOY2PageController::createLink("Page.Detail") ."/".$page->getId(), ENT_QUOTES, SOY2HTML::ENCODING).'" >';
		$html[] = '<img class="soycms-page-icon" src="' . htmlspecialchars($page->getIconUrl(), ENT_QUOTES, SOY2HTML::ENCODING). '"  alt="">';
		$html[] = '</a>';

		if($page->isBlog()){
			$html[] = '<img class="soycms-page-icon-blog" src="' . htmlspecialchars(SOY2PageController::createRelativeLink("./css/pagelist/images/blog.png"), ENT_QUOTES, SOY2HTML::ENCODING) . '">';
		}

		$statusIconPath = $this->getSubIconPath($page);
		if(strlen($statusIconPath)){
			$html[] = '<img class="soycms-page-icon-subicon" style="" src="' . htmlspecialchars($statusIconPath, ENT_QUOTES, SOY2HTML::ENCODING) . '">';
		}


		return implode("", $html);
	}

	/**
	 * ページ名（タイトル）
	 */
	private function buildPageTitle($page){
		return '<a class="soycms-page-title" href="'.htmlspecialchars(SOY2PageController::createLink("Page.Detail") ."/".$page->getId(), ENT_QUOTES, SOY2HTML::ENCODING).'" title="'.htmlspecialchars($page->getTitle(), ENT_QUOTES, SOY2HTML::ENCODING).'">'.htmlspecialchars(self::trimPageTitle($page->getTitle()), ENT_QUOTES, SOY2HTML::ENCODING).'</a>';
	}

	/**
	 * １つのページの編集メニュー
	 * @param unknown $page
	 * @return unknown
	 */
	private function buildPageMenu($page){
		$function = array();

		$function[] = '<a href="' . htmlspecialchars(SOY2PageController::createLink("Page.Detail") . "/" . $page->getId(), ENT_QUOTES, SOY2HTML::ENCODING). '">'.$this->getMessage("SOYCMS_EDIT").'</a>';

		$url = CMSUtil::getSiteUrl().$page->getUri();
// 		if( $page->getPageType() == Page::PAGE_TYPE_ERROR ){
// 			$function[] = '<a                  href="'.htmlspecialchars($url, ENT_QUOTES, SOY2HTML::ENCODING).'" target="_blank">'.$this->getMessage("SOYCMS_VIEW").'<i class="fa fa-external-link fa-fw" style="font-size:smaller"></i></a>';
// 		}else{
// 			$function[] = '<a class="disabled" href="'.htmlspecialchars($url, ENT_QUOTES, SOY2HTML::ENCODING).'" target="_blank">'.$this->getMessage("SOYCMS_VIEW").'<i class="fa fa-external-link fa-fw" style="font-size:smaller"></i></a>';
// 		}

		if($page->getIsTrash()){
			$function[] = '<a href="' .htmlspecialchars(SOY2PageController::createLink("Page.Remove")   . "/" . $page->getId(). "?soy2_token=" . soy2_get_token(), ENT_QUOTES, SOY2HTML::ENCODING). '" id="remove_' . $page->getId() . '" onclick="return confirm(\''.$this->getMessage("SOYCMS_CONFIRM_DELETE_COMPLETELY").'\');">'.$this->getMessage("SOYCMS_DELETE").'</a>';
			$function[] = '<a href="' .htmlspecialchars(SOY2PageController::createLink("Page.Recover")  . "/" . $page->getId(). "?soy2_token=" . soy2_get_token(), ENT_QUOTES, SOY2HTML::ENCODING). '" id="recover_' . $page->getId() . '">'.$this->getMessage("SOYCMS_RECOVER").'</a>';
		}elseif($page->isDeletable()){
			$function[] = '<a href="' .htmlspecialchars(SOY2PageController::createLink("Page.PutTrash") . "/" . $page->getId(). "?soy2_token=" . soy2_get_token(), ENT_QUOTES, SOY2HTML::ENCODING). '" id="put_trash_' . $page->getId() . '">'.$this->getMessage("SOYCMS_DELETE").'</a>';
		}

		if($page->isCopyable()){
			$function[] = '<a href="'.htmlspecialchars(SOY2PageController::createLink("Page.Copy.".$page->getId())."?soy2_token=" . soy2_get_token(), ENT_QUOTES, SOY2HTML::ENCODING). '">'.$this->getMessage("SOYCMS_COPY").'</a>';
		}

		$function[] = '<a href="'.htmlspecialchars(SOY2PageController::createLink("Page.Preview.".$page->getId()), ENT_QUOTES, SOY2HTML::ENCODING).'" target="_blank">'.$this->getMessage("SOYCMS_DYNAMIC_EDIT").'<i class="fa fa-external-link fa-fw" style="font-size:smaller"></i></a>';

		return implode("&nbsp;&nbsp;", $function);
	}

	private function trimPageTitle($title){
		return mb_strimwidth($title, 0, 100, "...", "UTF-8");
	}
}

class TreeListComponent extends HTMLList{

	function populateItem($entity){}
}
