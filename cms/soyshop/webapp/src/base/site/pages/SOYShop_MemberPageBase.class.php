<?php

class SOYShop_MemberPageBase extends SOYShopPageBase{

	private $user;
	private $nextUser;
	private $prevUser;
	private $currentIndex = 1;
	private $totalUserCount = 0;

	function build($args){

		$page = $this->getPageObject();
		$obj = $page->getPageObject();

		$alias = implode("/", $args);

		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $userDAO->getByAccountId($alias);
		}catch(Exception $e){
			try{
				$user = $userDAO->getByProfileId($alias);
			}catch(Exception $e){
				try{
					$user = $userDAO->getById($alias);
				}catch(Exception $e){
					header("HTTP/1.0 404 Not Found");
					echo "error";
					exit;
				}
			}
		}

		$this->setUser($user);

		//現在の商品を保存
		$obj->setCurrentUser($user);


//		try{
//			$logic = SOY2Logic::createInstance("logic.user.SearchUserUtil");
//			list($items, $total) = $logic->getById($item->getCategory());
//			$this->setTotalItemCount($total);

//			$counter = 1;
//			$prev_item = null;
//			$next_item = false;
//			foreach($items as $tmp){
//				if($tmp->getId() == $item->getId()){
//					$next_item = true;
//					continue;
//				}
//
//				if($next_item){
//					$next_item = $tmp;
//					break;
//				}
//				$prev_item = $tmp;
//				$counter++;
//			}
//
//			$this->setCurrentIndex($counter);
//			$this->setPrevItem($prev_item);
//			if($next_item && $next_item !== true) $this->setNextItem($next_item);

			//keywords
//			$keywords = $item->getAttribute("keywords");
//			if(strlen($keywords)) $this->getHeadElement()->insertMeta("keywords", $keywords . ",");
//
//			//description
//			$description = $item->getAttribute("description");
//			if(strlen($description)) $this->getHeadElement()->insertMeta("description", $description . " ");
//
//		}catch(Exception $e){
//			header("HTTP/1.0 500 Internal Server Error");
//			echo "error";
//			exit;
//		}

		//user
		$this->createAdd("user", "SOYShop_UserListComponent", array(
			"list" => array($user),
			"obj" => $obj,
			"soy2prefix" => "block",
		));

	}

	function getNextUser() {
		return $this->nextUser;
	}
	function setNextUser($nextUser) {
		$this->nextUser = $nextUser;
	}
	function getPrevUser() {
		return $this->prevUser;
	}
	function setPrevUser($prevUser) {
		$this->prevUser = $prevUser;
	}
	function getCurrentIndex() {
		return $this->currentIndex;
	}
	function setCurrentIndex($currentIndex) {
		$this->currentIndex = $currentIndex;
	}

	function getTotalUserCount() {
		return $this->totalUserCount;
	}
	function setTotalUserCount($totalUserCount) {
		$this->totalUserCount = $totalUserCount;
	}

	function getUser() {
		return $this->user;
	}
	function setUser($user) {
		$this->user = $user;
	}

	function getPager(){
		return new SOYShop_MemberPagePager($this);
	}
}

class SOYShop_MemberPagePager extends SOYShop_PagerBase{

	private $page;

	function __construct(SOYShop_MemberPageBase $page){
		$this->page = $page;
	}

	function getCurrentPage(){
		return $this->page->getCurrentIndex();
	}

	function getTotalPage(){
		return $this->page->getTotalUserCount();
	}

	function getLimit(){
		return 1;	//detail page's limiy is always 1;
	}

	private $_pagerUrl;

	function getPagerUrl(){
		if(!$this->_pagerUrl){
			$url = $this->page->getPageUrl();
			if($url[strlen($url) - 1] == "/") $url = substr($url, 0, strlen($url) - 1);
			$this->_pagerUrl = $url;
		}
		return $this->_pagerUrl;
	}

	function getNextPageUrl(){
		$url = $this->getPagerUrl();
		$page = $this->page;
		$nextUser = $page->getNextUser();
		if(!is_null($nextUser)){
			if($this->page->getPageObject()->getId() != $nextUser->getDetailPageId()){
				try{
					$uri = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getById($nextUser->getDetailPageId())->getUri();
					$url = soyshop_get_page_url($uri);
				}catch(Exception $e){
					$nextUser = null;
				}
			}
		}
		$next_link = ($nextUser) ? $url . "/" . ($nextUser->getAlias()) : "-";
		return $next_link;
	}

	function getPrevPageUrl(){
		$url = $this->getPagerUrl();
		$page = $this->page;
		$prevUser = $page->getPrevUser();
		if(!is_null($prevUser)){
			if($this->page->getPageObject()->getId() != $prevUser->getDetailPageId()){
				try{
					$uri = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getById($prevUser->getDetailPageId())->getUri();
					$url = soyshop_get_page_url($uri);
				}catch(Exception $e){
					$prevUser = null;
				}
			}
		}
		$prev_link = ($prevUser) ? $url . "/" . ($prevUser->getAlias()) : "-";
		return $prev_link;
	}

	function hasNext(){
		return ($this->page->getNextUser()) ? true : false;
	}

	function hasPrev(){
		return ($this->page->getPrevUser()) ? true : false;
	}
}
