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
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getUserId(){
		return $this->userId;
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
		$config = ItemReviewUtil::getConfig();

		$rank = $this->getEvaluation();
		$notRank = 5 - (int)$rank;
		//評価分
		$str1 = "";
		$str2 = "";
		for($i = 0; $i < $rank; $i++){
			$str1 .= "★";
		}
		for($j = 0; $j < $notRank; $j++){
			$str2 .= "☆";
		}
		return "<span style=\"color:#" . $config["code"] . ";\">" . $str1 . "</span>" . $str2;
	}

	function getApproval(){
		return $this->approval;
	}
	function setApproval($approval){
		$this->approval = $approval;
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
