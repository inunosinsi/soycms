<?php
SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReview");
/**
 * @entity SOYShop_ItemReview
 */
abstract class SOYShop_ItemReviewDAO extends SOY2DAO{

	/**
	 * @index id
	 * @order id desc
	 */
    abstract function get();

	/**
	 * @return object
	 */
   	abstract function getById($id);

   	/**
   	 * @return object
   	 * @query #id# = :id AND #userId# = :userId
   	 */
   	abstract function getByIdAndUserId($id, $userId);

   	/**
   	 * @return list
   	 */
   	abstract function getByItemId($itemId);

   	/**
   	 * @return list
   	 * @query item_id = :itemId AND is_approved = 1
   	 * @order create_date desc
   	 */
   	abstract function getIsApprovedByItemId($itemId);

   	/**
   	 * @return list
   	 * @order create_date desc
   	 */
   	abstract function getByUserId($userId);

	function getEvaluationAverageByItemId($itemId){
		$sql = "SELECT evaluation FROM soyshop_item_review ".
				"WHERE item_id = :itemId ".
				"AND is_approved = " . SOYShop_ItemReview::REVIEW_IS_APPROVED;

		try{
			$res = $this->executeQuery($sql, array(":itemId" => $itemId));
		}catch(Exception $e){
			return 0;
		}

		if(!count($res)) return 0;

		$count = count($res);	//投稿数
		$total = 0;	//合算

		foreach($res as $v){
			if(!isset($v["evaluation"])) continue;
			$total += (int)$v["evaluation"];
		}

		//平均	仮で切り捨て
		return (int)($total / $count);
	}

   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_ItemReview $bean);

	abstract function update(SOYShop_ItemReview $bean);

	/**
	 * @columns #id#,#isApproved#
	 */
	abstract function updateIsApproved($id, $isApproved);

	abstract function delete($id);
}
