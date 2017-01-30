<?php
/**
 * @table soyshop_review_point
 */
class SOYShop_ReviewPoint {
	
	/**
	 * @column review_id
	 */
	private $reviewId;
	private $point;
	
	function getReviewId(){
		return $this->reviewId;
	}
	function setReviewId($reviewId){
		$this->reviewId = $reviewId;
	}
	
	function getPoint(){
		return $this->point;
	}
	function setPoint($point){
		$this->point = $point;
	}
}
?>