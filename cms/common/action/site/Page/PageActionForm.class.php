<?php 
class PageActionForm extends SOY2ActionForm{
	
	private $id;
	private $uri;
	private $title;
	private $template;
	private $pageType;
	private $openPeriodStart;
    private $openPeriodEnd;
    private $isPublished;
    private $parentPageId;
    private $pageTitleFormat;
    private $icon;
    
	
	function getId() {
    	return $this->id;
    }
    
    /**
	 * @validator number
	 */
    function setId($id) {
    	$this->id = $id;
    }
    function getUri() {
    	return $this->uri;
    }
    
    /**
     * @validator string {"regex":"[^\\\/]$|^$"}
     */
    function setUri($uri) {
    	$this->uri = $uri;
    }
    
    function getTitle() {
    	return $this->title;
    }
    
    function setTitle($title) {
    	$this->title = $title;
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
    
    function getOpenPeriodStart() {
    	return $this->openPeriodStart;
    }
    
    /**
     * 今日以降
     */
    function setOpenPeriodStart($openPeriodStart) {
    	$tmpDate = (strlen($openPeriodStart)) ? strtotime($openPeriodStart) : false;
    	if($tmpDate === false){
    		$this->openPeriodStart = null;
    	}else{
    		$this->openPeriodStart = $tmpDate;
    	}
    }
    
    function getOpenPeriodEnd() {
    	return $this->openPeriodEnd;
    }
    
    /**
     * 今日以降
     */
    function setOpenPeriodEnd($openPeriodEnd) {
    	$tmpDate = (strlen($openPeriodEnd)) ? strtotime($openPeriodEnd) : false;
    	if($tmpDate === false){
    		$this->openPeriodEnd = null;	
    	}else{
    		$this->openPeriodEnd = $tmpDate;
    	}
    	 
    }
    function getIsPublished() {
    	return $this->isPublished;
    }
    
    /**
     * @validator number {"min":0,"max":1}
     */
    function setIsPublished($isPublished) {
    	$this->isPublished = $isPublished;
    }
    function getParentPageId() {
    	return $this->parentPageId;
    }
    function setParentPageId($parentPageId) {
    	$this->parentPageId = $parentPageId;
    }

    function getPageTitleFormat() {
    	return $this->pageTitleFormat;
    }
    function setPageTitleFormat($pageTitleFormat) {
    	$this->pageTitleFormat = $pageTitleFormat;
    }

    function getIcon() {
    	return $this->icon;
    }
    function setIcon($icon) {
    	$this->icon = $icon;
    }
}
?>
