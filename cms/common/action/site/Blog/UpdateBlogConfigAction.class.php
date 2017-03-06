<?php

class UpdateBlogConfigAction extends SOY2Action{
	
	var $id;
	
	function setId($id){
		$this->id = $id;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	
    	//生成フラグの変換
    	
    	if(
    		$form->entryPageUri == $form->categoryPageUri
    		|| $form->entryPageUri == $form->monthPageUri
    		|| $form->entryPageUri == $form->rssPageUri
    		|| $form->categoryPageUri == $form->monthPageUri
    		|| $form->categoryPageUri == $form->rssPageUri
    		|| $form->monthPageUri == $form->rssPageUri    	
    	){
    		return SOY2Action::FAILED;
    	}
    	
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	$page = $dao->getById($this->id);
    	$page = SOY2::cast($page,$form);
    	
    	//カテゴリ未選択の場合は、pageオブジェクトも未選択にする
    	if(is_null($form->getCategoryLabelList())) $page->setCategoryLabelList(null);    	
    	
    	$page->setId($this->id);

		try{
	    	$dao->updatePageConfig($page);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
    	
    	return SOY2Action::SUCCESS;	
    }
}

class UpdateBlogConfigActionForm extends SOY2ActionForm{
	
	var $uri;
	var $title;
	var $openPeriodStart;
    var $openPeriodEnd;
    var $isPublished;
    var $parentPageId;
    var $topPageUri;
	var $entryPageUri;
	var $monthPageUri;
	var $categoryPageUri;
	var $rssPageUri;
	var $topDisplayCount;
	var $monthDisplayCount;
	var $categoryDisplayCount;
	var $rssDisplayCount;
	var $topEntrySort;
	var $monthEntrySort;
	var $categoryEntrySort;
	var $generateTopFlag;
	var $generateEntryFlag;
	var $generateMonthFlag;
	var $generateCategoryFlag;
	var $generateRssFlag;
	var $blogLabelId;
	var $categoryLabelList = array();
	var $topTitleFormat;
	var $monthTitleFormat;
	var $entryTitleFormat;
	var $categoryTitleFormat;
	var $feedTitleFormat;
	var $icon;
	var $description;
	
    function setId($id) {
    	$this->id = $id;
    }
    function getUri() {
    	return $this->uri;
    }
    function setUri($uri) {
    	$this->uri = $uri;
    }
    function getTitle() {
    	return $this->title;
    }
    function setTitle($title) {
    	$this->title = $title;
    }
    function getOpenPeriodStart() {
    	return $this->openPeriodStart;
    }
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
    function setIsPublished($isPublished) {
    	$this->isPublished = $isPublished;
    }
    function getParentPageId() {
    	return $this->parentPageId;
    }
    function setParentPageId($parentPageId) {
    	$this->parentPageId = $parentPageId;
    }
    function getEntryPageUri() {
    	return $this->entryPageUri;
    }
    function setEntryPageUri($entryPageUri) {
    	$this->entryPageUri = $entryPageUri;
    }
    function getCategoryLabelList() {
    	return $this->categoryLabelList;
    }
    function setCategoryLabelList($categoryLabelList) {
    	$this->categoryLabelList = $categoryLabelList;
    }


    function getMonthPageUri() {
    	return $this->monthPageUri;
    }
    function setMonthPageUri($monthPageUri) {
    	$this->monthPageUri = $monthPageUri;
    }
    function getCategoryPageUri() {
    	return $this->categoryPageUri;
    }
    function setCategoryPageUri($categoryPageUri) {
    	$this->categoryPageUri = $categoryPageUri;
    }
    function getRssPageUri() {
    	return $this->rssPageUri;
    }
    function setRssPageUri($rssPageUri) {
    	$this->rssPageUri = $rssPageUri;
    }
    function getTopDisplayCount() {
    	return $this->topDisplayCount;
    }
    function setTopDisplayCount($topDisplayCount) {
    	$this->topDisplayCount = $topDisplayCount;
    }
    function getMonthDisplayCount() {
    	return $this->monthDisplayCount;
    }
    function setMonthDisplayCount($monthDisplayCount) {
    	$this->monthDisplayCount = $monthDisplayCount;
    }
    function getCategoryDisplayCount() {
    	return $this->categoryDisplayCount;
    }
    function setCategoryDisplayCount($categoryDisplayCount) {
    	$this->categoryDisplayCount = $categoryDisplayCount;
    }
    function getRssDisplayCount() {
    	return $this->rssDisplayCount;
    }
    function setRssDisplayCount($rssDisplayCount) {
    	$this->rssDisplayCount = $rssDisplayCount;
    }
    function getTopEntrySort(){
    	return $this->topEntrySort;
    }
    function setTopEntrySort($topEntrySort){
    	$this->topEntrySort = $topEntrySort;
    }
    function getMonthEntrySort(){
    	return $this->monthEntrySort;
    }
    function setMonthEntrySort($monthEntrySort){
    	$this->monthEntrySort = $monthEntrySort;
    }
    function getCategoryEntrySort(){
    	return $this->categoryEntrySort;
    }
    function setCategoryEntrySort($categoryEntrySort){
    	$this->categoryEntrySort = $categoryEntrySort;
    }
    function getGenerateTopFlag() {
    	return $this->generateTopFlag;
    }
    function setGenerateTopFlag($generateTopFlag) {
    	$this->generateTopFlag = (boolean)$generateTopFlag;
    }
    function getGenerateMonthFlag() {
    	return $this->generateMonthFlag;
    }
    function setGenerateMonthFlag($generateMonthFlag) {
    	$this->generateMonthFlag = (boolean)$generateMonthFlag;
    }
    function getGenerateCategoryFlag() {
    	return $this->generateCategoryFlag;
    }
    function setGenerateCategoryFlag($generateCategoryFlag) {
    	$this->generateCategoryFlag = (boolean)$generateCategoryFlag;
    }
    function getGenerateRssFlag() {
    	return $this->generateRssFlag;
    }
    function setGenerateRssFlag($generateRssFlag) {
    	$this->generateRssFlag = (boolean)$generateRssFlag;
    }

    function getGenerateEntryFlag() {
    	return $this->generateEntryFlag;
    }
    function setGenerateEntryFlag($generateEntryFlag) {
    	$this->generateEntryFlag = (boolean)$generateEntryFlag;
    }

    function getTopTitleFormat() {
    	return $this->topTitleFormat;
    }
    function setTopTitleFormat($topTitleFormat) {
    	$this->topTitleFormat = $topTitleFormat;
    }
    function getMonthTitleFormat() {
    	return $this->monthTitleFormat;
    }
    function setMonthTitleFormat($monthTitleFormat) {
    	$this->monthTitleFormat = $monthTitleFormat;
    }
    function getEntryTitleFormat() {
    	return $this->entryTitleFormat;
    }
    function setEntryTitleFormat($entryTitleFormat) {
    	$this->entryTitleFormat = $entryTitleFormat;
    }
    function getCategoryTitleFormat() {
    	return $this->categoryTitleFormat;
    }
    function setCategoryTitleFormat($categoryTitleFormat) {
    	$this->categoryTitleFormat = $categoryTitleFormat;
    }

    function getIcon() {
    	return $this->icon;
    }
    function setIcon($icon) {
    	$this->icon = $icon;
    }

    function getBlogLabelId() {
    	return $this->blogLabelId;
    }
    function setBlogLabelId($blogLabelId) {
    	$this->blogLabelId = $blogLabelId;
    }

    function getDescription() {
    	return $this->description;
    }
    function setDescription($description) {
    	$this->description = $description;
    }

    function getFeedTitleFormat() {
    	return $this->feedTitleFormat;
    }
    function setFeedTitleFormat($feedTitleFormat) {
    	$this->feedTitleFormat = $feedTitleFormat;
    }

    function getTopPageUri() {
    	return $this->topPageUri;
    }
    function setTopPageUri($topPageUri) {
    	$this->topPageUri = $topPageUri;
    }
}
?>