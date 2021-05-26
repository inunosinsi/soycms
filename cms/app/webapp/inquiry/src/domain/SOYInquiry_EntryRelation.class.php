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
	 * @column entry_id
	 */
	private $entryId;

	function getInquiryId(){
		return $this->inquiryId;
	}
	function setInquiryId($inquiryId){
		$this->inquiryId = $inquiryId;
	}

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}
}
