<?php

/**
 * @table Entry
 */
class Entry {

	const ENTRY_ACTIVE = 1;
	const ENTRY_OUTOFDATE = -1;
	const ENTRY_NOTPUBLIC = -2;

	const PERIOD_START = 0;
    const PERIOD_END = 2147483647;

	/**
	 * @id
	 */
    private $id;
   	private $title;
   	private $alias;
   	private $content;
   	private $more;
   	private $cdate;
   	private $udate;
   	private $openPeriodStart;
   	private $openPeriodEnd;
   	private $isPublished;
   	private $style;
   	private $author;
   	private $description;

   	/**
   	 * @no_persistent
   	 * 割り当てられているラベルIDを保存
   	 */
   	private $labels = array();

   	/**
   	 * @no_persistent
   	 */
   	private $url;

   	function getId() {
   		return $this->id;
   	}
   	function setId($id) {
   		$this->id = $id;
   	}
   	function getTitle() {
   		return $this->title;
   	}
   	function setTitle($title) {
   		$this->title = $title;
   	}
   	function getContent() {
   		return $this->content;
   	}
   	function setContent($content) {
   		$this->content = $content;
   	}
   	function getMore() {
   		return $this->more;
   	}
   	function setMore($more) {
   		$this->more = $more;
   	}
   	function getCdate() {

   		if(is_null($this->cdate)){
   			return time();
   		}

   		if(is_numeric($this->cdate)){
   			return $this->cdate;
   		}

   		return null;
   	}
   	function setCdate($cdate) {
   		$this->cdate = $cdate;
   	}
   	function getOpenPeriodStart() {
   		return $this->openPeriodStart;
   	}
   	function setOpenPeriodStart($openPeriodStart) {
   		$this->openPeriodStart = $openPeriodStart;
   	}

   	function getOpenPeriodEnd() {
   		return $this->openPeriodEnd;
   	}
   	function setOpenPeriodEnd($openPeriodEnd) {
   		$this->openPeriodEnd = $openPeriodEnd;
   	}
   	function getIsPublished() {
   		return $this->isPublished;
   	}
   	function setIsPublished($isPublished) {
   		$this->isPublished = (int)$isPublished;
   	}

	/**
	 * 設定されているラベルIDを返す
	 */
   	function getLabels() {
   		return $this->labels;
   	}

   	/**
   	 * 設定されているラベルIDを返す
   	 */
   	function setLabels($labels) {
   		$this->labels = $labels;
   	}


   	function getUdate() {
   		return $this->udate;
   	}
   	function setUdate($udate) {
   		$this->udate = $udate;
   	}

   	function getAlias() {
   		if(strlen($this->alias)<1){
   			return $this->getId();
   		}
   		return $this->alias;
   	}
   	function setAlias($alias) {
   		$this->alias = $alias;
   	}
   	function isEmptyAlias(){
   		return (strlen($this->alias)==0);
   	}

   	function getStyle() {
   		return $this->style;
   	}
   	function setStyle($style) {
   		$this->style = $style;
   	}

   	/**
   	 * 現在このエントリーの状態を返します
   	 * @return ENTRY_ACTIVE 公開状態
   	 * @return ENTRY_OUTOFDATE 期間外
   	 * @return ENTRY_NOTPUBLIC 未公開状態
   	 *
   	 * if(isActive() > 0){
   	 	 //公開状態のときの処理
   	 } else{
   	 	 //未公開状態の時の処理
   	 }
   	 */
   	function isActive(){
   		if(!$this->isPublished){
   			return self::ENTRY_NOTPUBLIC;
   		}
   		$now = time();

   		/*
   		 * CMSUtil::decodeDateによってopenPeriodStartとopenPeriodEndのDATE_MIN/DATE_MAXはnullに書き換えられている
   		 */
   		if(
   		  (is_null($this->openPeriodStart) || $this->openPeriodStart <= $now)
   		  &&
   		  (is_null($this->openPeriodEnd)   || $now < $this->openPeriodEnd)
   		){
   			return self::ENTRY_ACTIVE;
   		}else{
   			return self::ENTRY_OUTOFDATE;
   		}
   	}

   	function getStateMessage(){
   		switch($this->isActive()){
   			case self::ENTRY_ACTIVE:
   				return CMSMessageManager::get("SOYCMS_STAY_PUBLISHED");
   			case self::ENTRY_OUTOFDATE:
   				return CMSMessageManager::get("SOYCMS_OUTOFDATE");
   			case self::ENTRY_NOTPUBLIC:
   				return CMSMessageManager::get("SOYCMS_NOT_PUBLISHED");
   			default:
   				return CMSMessageManager::get("SOYCMS_OMG");
   		}
   	}

   	function getDescription() {
   		return $this->description;
   	}
   	function setDescription($description) {
   		$this->description = $description;
   	}

   	function getAuthor() {
   		return $this->author;
   	}
   	function setAuthor($author) {
   		$this->author = $author;
   	}


   	function getUrl() {
   		return $this->url;
   	}
   	function setUrl($url) {
   		$this->url = $url;
   	}
}
