<?php
/**
 * @table Page
 */
class Page {

	const PAGE_TYPE_NORMAL	=	0;			//標準ページ
	const PAGE_TYPE_MOBILE	=	100;		//携帯用ページ
	const PAGE_TYPE_APPLICATION = 150;		//アプリケーションページ
	const PAGE_TYPE_BLOG	= 		200;	//ブログページ
	const PAGE_TYPE_ERROR	=		300;	//エラーページ

	const PAGE_ACTIVE = 1;
	const PAGE_OUTOFDATE = -1;
	const PAGE_NOTPUBLIC = -2;

	const PAGE_OUTOFDATE_BEFORE = -3;
	const PAGE_OUTOFDATE_PAST   = -4;

	const PAGE_ACTIVE_CLOSE_FUTURE = 2;
	const PAGE_ACTIVE_CLOSE_BEFORE = 3;

	/**
	 * @id
	 */
    private $id;
    private $uri;

    private $title;
    private $template;

    private $isTrash;

    /**
     * @column page_type
     */
    private $pageType;

    /**
     * @column page_config
     */
    private $pageConfig;

    /**
     * @no_persistent
     */
    private $_pageConfig;

    private $openPeriodStart;
    private $openPeriodEnd;

    private $isPublished;
    private $udate;

    /**
     * @column parent_page_id
     */
    private $parentPageId;

	//親子関係保持用
	/**
	 * @no_persistent
	 */
	private $childPages = array();

	/**
	 * @no_persistent
	 */
	private $parentPage;

	/**
	 * @no_persistent
	 */
	private $pageTitleFormat;

	private $icon;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getUri() {
    	return (string)$this->uri;
    }
    function setUri($uri) {
    	$this->uri = $uri;
    }
    function getTemplate() {
    	return $this->template;
    }
    function setTemplate($template) {
    	$this->template = $template;
    }
    function getPageType() {
    	return $this->pageType;
    }
    function setPageType($pageType) {
    	$this->pageType = $pageType;
    }

    function getPageConfig() {
    	return $this->pageConfig;
    }

	function setPageConfig($pageConfig) {

    	if(is_object($pageConfig)){
    		$this->pageConfig = serialize($pageConfig);
    		$this->_pageConfig = $pageConfig;
    	}else{
    		$this->pageConfig = $pageConfig;
    		if( strlen($pageConfig) && strpos($pageConfig, 'O:8:"stdClass"') === 0){
    			$this->_pageConfig = unserialize($pageConfig);
    		}
    	}
    }

    function getPageConfigObject(){
    	if(is_null($this->_pageConfig)){
    		$this->_pageConfig = new stdClass();
    		$this->pageConfig = serialize($this->_pageConfig);
    	}
    	return $this->_pageConfig;
    }

    function getOpenPeriodStart() {
    	return CMSUtil::decodeDate($this->openPeriodStart);
    }
    function setOpenPeriodStart($openPeriodStart) {
    	$this->openPeriodStart = $openPeriodStart;
    }
    function getOpenPeriodEnd() {
    	return CMSUtil::decodeDate($this->openPeriodEnd);
    }
    function setOpenPeriodEnd($openPeriodEnd) {
    	$this->openPeriodEnd = $openPeriodEnd;
    }
    function getIsPublished() {
    	if(is_null($this->isPublished)){
    		return 0;
    	}else{
    		return $this->isPublished;
    	}

    }
    function setIsPublished($isPublished) {
    	if(is_null($isPublished)){
    		$this->isPublished = 0;
    	}else{
    		$this->isPublished = $isPublished;
    	}
    }
    function getParentPageId() {
    	return $this->parentPageId;
    }
    function setParentPageId($parentPageId) {
    	if(is_null($parentPageId) || strlen($parentPageId) != 0){
    		$this->parentPageId = $parentPageId;
    	}
    }
    function getTitle(){
    	return $this->title;
    }
    function setTitle($title){
    	$this->title = $title;
    }


    /**
     * 子ページを返す
     */
    function getChildPages() {
    	if(is_array($this->childPages)){
    		ksort($this->childPages);
    	}
    	return $this->childPages;
    }
    function addChildPage($page) {
    	$this->childPages[$page->getId()] = $page;
    	$page->setParentPage($this);
    }

    /**
     * @param $childId
     */
    function getNodePathCount($step = null){

    	if($step === null){
    		$count = count($this->childPages);
    	}else{
    		$count = 0;
    	}
    	$counter = 0;

    	foreach($this->childPages as $key => $childPage){
    		if(!is_null($step) && $counter > $step){
    			break;
    		}

    		$tmp = $childPage->getNodePathCount();
    		$count += max($tmp-1,0);
    		$counter++;
    	}
    	return $count;
    }

    /**
     * 親ページを返す
     */
    function getParentPage() {
    	return $this->parentPage;
    }
    function setParentPage($parentPage) {
    	$this->parentPage = $parentPage;
    }

    function getUdate() {
    	return CMSUtil::decodeDate($this->udate);
    }
    function setUdate($udate) {
    	$this->udate = CMSUtil::encodeDate($udate,true);
    }
    function getIsTrash() {
    	if(is_null($this->isTrash)){
    		return 0;
    	}else{
    		return $this->isTrash;
    	}
    }
    function setIsTrash($isTrash) {
    	if(is_null($isTrash)){
    		$this->isTrash = 0;
    	}else{
    		$this->isTrash = $isTrash;
    	}
    }

    /**
     * 削除できるかどうか
     * @return boolean
     */
    function isDeletable(){
    	if($this->pageType == Page::PAGE_TYPE_ERROR){
			if($this->id >1 && SOY2Logic::createInstance("logic.site.Page.PageLogic")->hasMultipleErrorPage() ){
				return true;
			}else{
	    		return false;
    		}
    	}else{
    		return true;
    	}
    }

    /**
     * 複製できるかどうか
     * @return boolean
     */
    function isCopyable(){
    	if($this->pageType == Page::PAGE_TYPE_ERROR){
    		return false;
    	}else{
    		return true;
    	}
    }

    /**
   	 * 現在このページの状態を返します
   	 * @return PAGE_ACTIVE 公開状態
   	 * @return PAGE_OUTOFDATE 期間外
   	 * @return PAGE_NOTPUBLIC 未公開状態
   	 *
   	 * if(isActive() > 0){
   	 	 //公開状態のときの処理
   	 } else{
   	 	 //未公開状態の時の処理
   	 }
   	 */
   	function isActive($with_before_after = false){
		if(!$this->isPublished){
   			return self::PAGE_NOTPUBLIC;
   		}
   		$now = time();
   		$start = CMSUtil::encodeDate($this->openPeriodStart,true);
   		$end   = CMSUtil::encodeDate($this->openPeriodEnd,false);



   		if($start < $now && $end > $now){
   			if($with_before_after){
	   			if($end != CMSUtil::encodeDate(null,false)){
	   				return self::PAGE_ACTIVE_CLOSE_FUTURE;
	   			}else if($start != CMSUtil::encodeDate(null,true)){
	   				return self::PAGE_ACTIVE_CLOSE_BEFORE;
	   			}else{
	   				return self::PAGE_ACTIVE;
	   			}
   			}else{
   				return self::PAGE_ACTIVE;
   			}
   		}else{
   			if($with_before_after){
   				if($start >= $now){
					return self::PAGE_OUTOFDATE_BEFORE;
   				}else{
   					return self::PAGE_OUTOFDATE_PAST;
   				}
   			}else{
   				return self::PAGE_OUTOFDATE;
   			}
   		}
   	}



   	/**
   	 * ブログかどうかを返す
   	 */
   	function isBlog(){
   		if($this->pageType == Page::PAGE_TYPE_BLOG){
   			return true;
   		}else{
   			return false;
   		}
   	}

   	/**
   	 * 携帯ページかどうか
   	 */
   	function isMobile(){
   		if($this->pageType == Page::PAGE_TYPE_MOBILE){
   			return true;
   		}else{
   			return false;
   		}
   	}

   	function getStateMessage(){
   		switch($this->isActive()){
   			case self::PAGE_ACTIVE:
   				return CMSMessageManager::get("SOYCMS_STAY_PUBLISHED");
   			case self::PAGE_OUTOFDATE:
   				return CMSMessageManager::get("SOYCMS_OUTOFDATE");
   			case self::PAGE_NOTPUBLIC:
   				return CMSMessageManager::get("SOYCMS_NOT_PUBLISHED");
   			default:
   				return CMSMessageManager::get("SOYCMS_OMG");
   		}
   	}

	function getPageTitleFormat() {
   		$config = $this->getPageConfigObject();
   		if(!property_exists($config, "PageTitleFormat")){
   			$config->PageTitleFormat = "";
   		}
   		return $config->PageTitleFormat;
   	}
   	function setPageTitleFormat($pageTitleFormat) {
   		$pageConfig = $this->getPageConfigObject();
   		$pageConfig->PageTitleFormat = $pageTitleFormat;
   		$this->setPageConfig($pageConfig);
   	}

   	function getIcon() {
   		return $this->icon;
   	}
   	function setIcon($icon) {
   		$this->icon = $icon;
   	}

   	function getIconUrl(){

		$icon = $this->getIcon();

		if($this->getPageType() == Page::PAGE_TYPE_ERROR)$icon = "notfound.gif";
		if(!$icon && $this->getPageType() == Page::PAGE_TYPE_BLOG)$icon = "blog_default.gif";
		if(!$icon)$icon = "page_default.gif";
		//if($this->getIsTrash())$icon = "deleted.gif";

		return CMS_PAGE_ICON_DIRECTORY_URL . $icon;

	}
}
