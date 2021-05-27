<?php
/**
 * @table soyinquiry_entry_relation
 */
class SOYInquiry_EntryRelation {

	/**
	 * @column inquiry_id
	 */
	private $inquiryId;

	/**
	 * @column site_id
	 */
	private $siteId;

	/**
	 * @column page_id
	 */
	private $pageId;

	/**
	 * @column entry_id
	 */
	private $entryId;

	function getInquiryId(){
		return $this->inquiryId;
	}
	function setInquiryId($inquiryId){
		$this->inquiryId = $inquiryId;
	}

	function getSiteId(){
		return $this->siteId;
	}
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	function getPageId(){
		return $this->pageId;
	}
	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}
}
