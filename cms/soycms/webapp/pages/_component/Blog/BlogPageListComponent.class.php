<?php

class BlogPageListComponent extends HTMLList {

	private $blogIcon;
	private $deletedIcon;
	private $notopenIcon;
	private $draftIcon;
	private $grayIcon;
	private $greenIcon;

	private function _getDeletedIcon(){
		if(!$this->deletedIcon) $this->deletedIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/cross.png");
		return $this->deletedIcon;
	}

	private function _getDraftIcon(){
		if(!$this->draftIcon) $this->draftIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/draft.gif");
		return $this->draftIcon;
	}

	private function _getGrayIcon(){
		if(!$this->grayIcon) $this->grayIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/after.gif");
		return $this->grayIcon;
	}

	private function _getGreenIcon(){
		if(!$this->greenIcon) $this->greenIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/before.gif");
		return $this->greenIcon;
	}

	private function _getSubIcon($page){
		$visible = ($page->getPageType() != Page::PAGE_TYPE_ERROR);
		$src = "";

		if($page->getIsTrash()){
			$src = self::_getDeletedIcon();
		}else{
			switch($page->isActive(true)){
				case Page::PAGE_ACTIVE:
				case Page::PAGE_ACTIVE_CLOSE_BEFORE:
					$visible = false;
					$src = "";
					break;
				case Page::PAGE_ACTIVE_CLOSE_FUTURE:
					$src = self::_getGreenIcon();
					break;
				case Page::PAGE_OUTOFDATE_BEFORE:
					$src = self::_getGrayIcon();
					break;
				case Page::PAGE_OUTOFDATE_PAST:
				case Page::PAGE_NOTPUBLIC:
					$src = self::_getDraftIcon();
					break;
			}
		}
		return array($src,$visible);
	}

	private function _getBlogIcon(){
		if(!$this->blogIcon) $this->blogIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/blog.png");
		return $this->blogIcon;
	}

	public function populateItem($entity){
		$pageType = (int)$entity->getPageType();

		$pageUrl = (strlen($entity->getUri()) >0) ? "/{$entity->getUri()}/" : "/" ;
		$pageFullUrl = CMSUtil::getSiteUrl() . ( (strlen($entity->getUri()) >0) ? $entity->getUri() ."/" : "" ) ;

		$this->addImage("page_icon", array(
			"src" => $entity->getIconUrl(),
		));

		$this->addLink("title", array(
			"text"=> mb_strimwidth($entity->getTitle(),0,44,"..."),
			"link"=>SOY2PageController::createLink("Blog") ."/".$entity->getId()
		));

		$this->addLink("page_url", array(
			"text" => $pageUrl,
			"link" => $pageFullUrl,
		));

		$this->addLabel("uri", array(
			"text" => $entity->getUri()
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($entity->getUdate())) ? date('Y-m-d', $entity->getUdate()) : ""
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink("Blog") ."/".$entity->getId(),
			"visible" => (UserInfoUtil::hasEntryPublisherRole()),
		));
		$this->addLink("entry_link", array(
			"link" => SOY2PageController::createLink("Blog.EntryList") ."/".$entity->getId()
		));

		$this->addLink("post_entry_link", array(
			"link" => SOY2PageController::createLink("Blog.Entry") ."/".$entity->getId()
		));

		$this->addLink("config_link", array(
			"link" => SOY2PageController::createLink("Blog.Config") ."/".$entity->getId(),
			"visible" => UserInfoUtil::hasSiteAdminRole()
		));
		$this->addLink("template_link", array(
			"link" => SOY2PageController::createLink("Blog.Template") ."/".$entity->getId(),
			"visible" => (UserInfoUtil::hasSiteAdminRole())
		));

		$this->addLink("trackback_link", array(
			"link" => SOY2PageController::createLink("Blog.Trackback") ."/".$entity->getId(),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),//記事公開権限のある場合のみ
		));
		$this->addLink("comment_link", array(
			"link" => SOY2PageController::createLink("Blog.Comment") ."/".$entity->getId(),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),//記事公開権限のある場合のみ
		));
		$this->addLink("category_link", array(
			"link" => SOY2PageController::createLink("Blog.Category") ."/".$entity->getId(),
			"visible" => !UserInfoUtil::hasSiteAdminRole(),//記事公開権限のある場合のみ
		));

		$trashLink = "";
		if($entity->getIsTrash() == 1){
			$trashLink = SOY2PageController::createLink("Blog.RemoveBlog") . "/" . $entity->getId();
			$onclick= "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_DELETE_COMPLETELY")."');";
		}else{
			$trashLink = SOY2PageController::createLink("Blog.PutTrashBlog") . "/" . $entity->getId();
			$onclick = "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_MOVE_INTO_TRASHBOX")."');";
		}
		$this->addActionLink("delete_link", array(
			"link" => $trashLink,
			"onclick" => $onclick,
			"visible" => ($entity->isDeletable() && UserInfoUtil::hasSiteAdminRole())
		));

		$this->addActionLink("recover_link", array(
			"link" => SOY2PageController::createLink("Blog.RecoverBlog") . "/" . $entity->getId(),
			'onclick' => "return confirm('".CMSMessageManager::get("SOYCMS_CONFIRM_RECOVER_WEBPAGE")."');",
			"visible" => ($entity->getIsTrash() && UserInfoUtil::hasSiteAdminRole())
		));

		$this->addLink("preview_link", array(
			"link"=>SOY2PageController::createLink("Page.Preview.".$entity->getId())
		));

		$this->addLink("client_view", array(
			"link" => $pageFullUrl,
			"visible" => ($entity->isActive() > 0)
		));

		//公開してなかったら×を表示
		list($src,$visible) = self::_getSubIcon($entity);
		$this->addImage("is_deleted", array(
			"src" => $src,
			"visible" => $visible
		));
	}
}
