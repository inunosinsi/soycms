<?php

/**
 * @table URLShortener
 */
class URLShortener extends SOY2DAO_EntityBase {
	
	const TYPE_ENTRY = 1; //entry
	const TYPE_PAGE = 2;//page
	
	/**
	 * @id
	 */
    private $id;
    
    /**
     * @column url_from
     */
    private $from;
    
    /**
     * @column url_to
     */
    private $to;
    
    /**
     * @column target_type
     */
    private $targetType;
    
    /**
     * @column target_id
     */
    private $targetId;

    
   	private $title;
   	private $memo;

   	/**
   	 * @todo soy2_serialize
   	 */
   	private $attr;
   	private $cdate;
   	private $udate;
 
	function check(){
		
		if(empty($this->from) || empty($this->to)){
			return false;
		}

		return true;
	}
	
   	public function getId() {
   		return $this->id;
   	}
   	public function setId($id) {
   		$this->id = $id;
   	}

   	public function getFrom() {
   		return $this->from;
   	}
   	public function setFrom($from) {
   		$this->from = $from;
   	}

   	public function getTo() {
   		return $this->to;
   	}
   	public function setTo($to) {
   		$this->to = $to;
   	}
   	public function getTargetType() {
   		return $this->targetType;
   	}
   	public function setTargetType($targetType) {
   		$this->targetType = $targetType;
   	}

   	public function getTargetId() {
   		return $this->targetId;
   	}
   	public function setTargetId($targetId) {
   		$this->targetId = $targetId;
   	}
   	public function getTitle() {
   		return $this->title;
   	}
   	public function setTitle($title) {
   		$this->title = $title;
   	}

   	public function getMemo() {
   		return $this->memo;
   	}
   	public function setMemo($memo) {
   		$this->memo = $memo;
   	}

   	public function getAttr() {
   		return $this->attr;
   	}
   	public function setAttr($attr) {
   		$this->attr = $attr;
   	}

   	public function getCdate() {
   		return $this->cdate;
   	}
   	public function setCdate($cdate) {
   		$this->cdate = $cdate;
   	}

   	public function getUdate() {
   		return $this->udate;
   	}
   	public function setUdate($udate) {
   		$this->udate = $udate;
   	}
   	
   	/* Util */
   	public function getAttrArray() {
   		if(!soy2_unserialise($this->attr)){
   			return array();
   		}
   		return soy2_unserialise($this->attr);
   	}
   	public function setAttrArray($attr) {
   		$this->attr = soy2_serialize($attr);
   	}

}
?>