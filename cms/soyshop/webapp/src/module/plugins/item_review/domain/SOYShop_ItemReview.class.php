<?php
/**
 * @table soyshop_item_review
 */
class SOYShop_ItemReview {

	const REVIEW_NO_APPROVED = 0;
	const REVIEW_IS_APPROVED = 1;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column user_id
	 */
	private $userId;
	private $nickname;
	private $title;
	private $content;
	private $image;
	private $movie;
	private $evaluation;
	private $approval;
	private $vote;
	private $attributes;


	/**
	 * @column is_approved
	 */
	private $isApproved;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getItemId(){
		return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getNickname(){
		return $this->nickname;
	}
	function setNickname($nickname){
		$this->nickname = $nickname;
	}

	function getTitle(){
		return $this->title;
	}
	function setTitle($title){
		$this->title = $title;
	}

	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}

	function getImage(){
		return $this->image;
	}
	function setImage($image){
		$this->image = $image;
	}

	function getMovie(){
		return $this->movie;
	}
	function setMovie($movie){
		$this->movie = $movie;
	}

	function getEvaluation(){
		return $this->evaluation;
	}
	function setEvaluation($evaluation){
		$this->evaluation = $evaluation;
	}

	function getEvaluationString(){
		SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
		return ItemReviewUtil::buildEvaluationString($this->getEvaluation());
	}

	function getApproval(){
		return $this->approval;
	}
	function setApproval($approval){
		$this->approval = $approval;
	}

	function getVote(){
		return $this->vote;
	}
	function setVote($vote){
		$this->vote = $vote;
	}

	function getAttributes(){
		return $this->attributes;
	}
	function setAttributes($attributes){
		$this->attributes = $attributes;
	}

	function getIsApproved(){
		return $this->isApproved;
	}
	function setIsApproved($isApproved){
		$this->isApproved = $isApproved;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}

	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}

    /**
     * テーブル名を取得
     */
    public static function getTableName(){
    	return "soyshop_item_review";
    }
}
