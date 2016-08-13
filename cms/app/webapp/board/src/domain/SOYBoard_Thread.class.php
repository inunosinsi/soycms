<?php
/**
 * @table soyboard_thread
 */
class SOYBoard_Thread{
	
	/**
	 * @id
	 */
	private $id;
	private $title;
	private $owner;
	
	/**
	 * @column page_id
	 */
	private $pageId;
	
	/**
	 * @no_persistent
	 */
	private $response;
	private $cdate;
	private $lastsubmitdate;
	/**
	 * @column sort_date
	 */
	private $sortDate;//ソート用の日付　通常は投稿があると更新、sageがつくと更新しない
	private $readonly; //true の場合は過去ログ、　false　の場合は現行すれ
	

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
	function getOwner() {
		return $this->owner;
	}
	function setOwner($owner) {
		$this->owner = $owner;
	}
	function getResponse() {
		return $this->response;
	}
	function setResponse($response) {
		$this->response = $response;
	}
	function getCdate() {
		return $this->cdate;
	}
	function setCdate($cdate) {
		$this->cdate = $cdate;
	}
	function getLastsubmitdate() {
		return $this->lastsubmitdate;
	}
	function setLastsubmitdate($lastsubmitdate) {
		$this->lastsubmitdate = $lastsubmitdate;
	}
	function getReadonly() {
		return $this->readonly;
	}
	function setReadonly($readonly) {
		$this->readonly = $readonly;
	}

	function getSortDate() {
		return $this->sortDate;
	}
	function setSortDate($sortDate) {
		$this->sortDate = $sortDate;
	}

	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}
?>