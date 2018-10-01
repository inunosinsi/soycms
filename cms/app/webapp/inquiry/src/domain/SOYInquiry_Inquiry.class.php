<?php
/**
 * @table soyinquiry_inquiry
 */
class SOYInquiry_Inquiry {

	const FLAG_NEW     = 0;//未読
	const FLAG_READ    = 1;//既読
	const FLAG_DELETED = 2;//削除済み

	const COMMENT_HAS = 1;	//コメント有り
	const COMMENT_NONE = 2;	//コメント無し

	/**
	 * @id
	 */
    private $id;

    /**
     * @column tracking_number
     */
    private $trackingNumber;

    /**
     * @column form_id
     */
    private $formId;

	/**
	 * @column ip_address
	 */
	private $ipAddress;

    private $content;

    private $data;

    private $flag = 0;	//未読

    /**
     * @column create_date
     */
    private $createDate;

	/**
	 * @column form_url
	 */
	private $formUrl;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getFormId() {
    	return $this->formId;
    }
    function setFormId($formId) {
    	$this->formId = $formId;
    }
	function getIpAddress(){
		return $this->ipAddress;
	}
	function setIpAddress($ipAddress){
		$this->ipAddress = $ipAddress;
	}
    function getContent() {
    	return $this->content;
    }
    function setContent($content) {
    	$this->content = $content;
    }
    function getCreateDate() {
    	return $this->createDate;
    }
    function setCreateDate($createDate) {
    	$this->createDate = $createDate;
    }

    function getData() {
    	return $this->data;
    }
    function setData($data) {
    	$this->data = $data;
    }

    function getDataArray(){
    	return unserialize($this->data);
    }

    function getFlag() {
    	return $this->flag;
    }
    function setFlag($flag) {
    	$this->flag = $flag;
    }

    function getFlagText(){
    	switch($this->flag){
    		case self::FLAG_NEW :
    			return "未読";
    		case self::FLAG_READ :
    			return "既読";
    		case self::FLAG_DELETED :
    			return "削除済";
    	}
    }

    /**
     * 未読かどうか
     * @return boolean
     */
    function isUnread(){
    	return ($this->flag == 0);
    }

    function getFormUrl() {
    	return $this->formUrl;
    }
    function setFormUrl($formUrl) {
    	$this->formUrl = $formUrl;
    }

    function getTrackingNumber() {
    	return $this->trackingNumber;
    }
    function setTrackingNumber($trackingNumber) {
    	$this->trackingNumber = $trackingNumber;
    }
}
