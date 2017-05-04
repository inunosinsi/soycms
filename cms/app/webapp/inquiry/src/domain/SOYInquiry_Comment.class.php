<?php
/**
 * @table soyinquiry_comment
 */
class SOYInquiry_Comment {

	/**
	 * @id
	 */
    private $id;
    
    /**
     * @column inquiry_id
     */
    private $inquiryId;
    
    private $title;
    
    private $author;
    
    private $content;
    
    /**
     * @column create_date
     */
    private $createDate;    

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getInquiryId() {
    	return $this->inquiryId;
    }
    function setInquiryId($inquiryId) {
    	$this->inquiryId = $inquiryId;
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
    function getContent() {
    	return $this->content;
    }
    function setContent($content) {
    	$this->content = $content;
    }
    function getCreateDate() {
    	if(!$this->createDate)return time();
    	return $this->createDate;
    }
    function setCreateDate($createDate) {
    	$this->createDate = $createDate;
    }
}
?>