<?php

/**
 * @table EntryComment
 */
class EntryComment {

	/**
	 * @id
	 */
    private $id;

    /**
     * @column entry_id
     */
    private $entryId;
   	private $title;
   	private $author;
  	private $body;

  	/**
  	 * @column submitdate
  	 */
  	private $submitDate;

    /**
     * @column is_approved
     */
	private $isApproved;

	/**
	 * @column mail_address
	 */
	private $mailAddress;

	private $url;


   	function getId() {
   		return $this->id;
   	}
   	function setId($id) {
   		$this->id = $id;
   	}
   	function getEntryId() {
   		return $this->entryId;
   	}
   	function setEntryId($entryId) {
   		$this->entryId = $entryId;
   	}
   	function getTitle() {
   		return $this->title;
   	}
   	function setTitle($title) {
   		$this->title = $title;
   	}
   	function getAuthor() {
   		return $this->author;
   	}
   	function setAuthor($author) {
   		$this->author = $author;
   	}
   	function getBody() {
   		return $this->body;
   	}
   	function setBody($body) {
   		$this->body = $body;
   	}


   	function getSubmitDate() {
   		return $this->submitDate;
   	}
   	function setSubmitDate($submitDate) {
   		$this->submitDate = $submitDate;
   	}
   	function getIsApproved() {
   		return $this->isApproved;
   	}
   	function setIsApproved($isApproved) {
   		$this->isApproved = $isApproved;
   	}

   	function getUrl() {
   		return $this->url;
   	}
   	function setUrl($url) {
   		$this->url = $url;
   	}

   	function getMailAddress() {
   		return $this->mailAddress;
   	}
   	function setMailAddress($mailAddress) {
   		$this->mailAddress = $mailAddress;
   	}
}
