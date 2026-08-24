<?php
/**
 * @table TemplateHistory
 */
class TemplateHistory {
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column page_id
	 */
	private $pageId;

	private $author;

	/**
	 * @column user_id
	 */
	private $userId;
	
	private $contents;
	
	/**
	 * @column update_date
	 */
	private $updateDate;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getPageId() {
		return $this->pageId;
	}
	function getAuthor(){
		return $this->author;
	}
	function setAuthor($author){
		$this->author;
	}
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
	function getContents() {
		return $this->contents;
	}
	function setContents($contents) {
		$this->contents = $contents;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}

}
?>
