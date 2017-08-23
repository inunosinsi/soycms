<?php
/**
 * @table EntryTrackback
 */
class EntryTrackback {
	
	/**
	 * @id
	 */
    private $id;
    
    /**
     * @column entry_id
     */
    private $entryId;
    
    private $excerpt;
    
    private $url;
    
    private $title;
    
    /**
     * @column blog_name
     */
    private $blogName;
    
    private $certification;
    
    private $submitdate;

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
    function getExcerpt() {
    	return $this->excerpt;
    }
    function setExcerpt($excerpt) {
    	$this->excerpt = $excerpt;
    }
    function getUrl() {
    	return $this->url;
    }
    function setUrl($url) {
    	$this->url = $url;
    }
    function getBlogName() {
    	return $this->blogName;
    }
    function setBlogName($blogName) {
    	$this->blogName = $blogName;
    }
    function getCertification() {
    	return $this->certification;
    }
    function setCertification($certification) {
    	$this->certification = $certification;
    }


    function getSubmitdate() {
    	return $this->submitdate;
    }
    function setSubmitdate($submitdate) {
    	$this->submitdate = $submitdate;
    }

    function getTitle() {
    	return $this->title;
    }
    function setTitle($title) {
    	$this->title = $title;
    }
}
?>