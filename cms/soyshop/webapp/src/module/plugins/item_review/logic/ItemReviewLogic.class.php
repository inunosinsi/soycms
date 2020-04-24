<?php
class ItemReviewLogic extends SOY2LogicBase{

	private $page;

	function __construct(){
		SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
	}

	/**
	 * 投稿されたレビューを登録する
	 * @param array
	 */
	function registerReview($array){
		$reviewDao = self::_reviewDao();
		$review = SOY2::cast("SOYShop_ItemReview", (object)$array);

		$config = ItemReviewUtil::getConfig();

		//値を挿入する
		$review->setItemId(self::_getItemId());
		$review->setUserId(self::_getUserId());

		$isApproved = (!is_null($config["publish"])) ? SOYShop_ItemReview::REVIEW_IS_APPROVED : SOYShop_ItemReview::REVIEW_NO_APPROVED;
		$review->setIsApproved($isApproved);
		$review->setCreateDate(time());
		$review->setUpdateDate(time());

		try{
			$reviewId = $reviewDao->insert($review);
		}catch(Exception $e){
			return false;
		}

		//ポイント加算設定があるか調べる
		SOY2::import("util.SOYShopPluginUtil");
		if(!is_null($review) && isset($config["point"]) && (int)$config["point"] > 0 && SOYShopPluginUtil::checkIsActive("common_point_base")){
			//公開の場合はここで加算
			if($isApproved){
				$history = "レビュー投稿で" . $config["point"] . "ポイント追加";
				SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->insert($config["point"], $history, $review->getUserId());
			//非公開の場合はデータベースにプッシュしておく
			}else{
				SOY2::import("module.plugins.item_review.domain.SOYShop_ReviewPointDAO");
				$dao = SOY2DAOFactory::create("SOYShop_ReviewPointDAO");
				$obj = new SOYShop_ReviewPoint();
				$obj->setReviewId($reviewId);
				$obj->setPoint($config["point"]);
				try{
					$dao->insert($obj);
				}catch(Exception $e){
					var_dump($e);
					//
				}
			}
		}

		return true;
	}

	function getReviews($cnt=null){
		$reviewDao = self::_reviewDao();
		if(is_numeric($cnt)) $reviewDao->setLimit($cnt);
		$reviewDao->setOrder("create_date DESC");
		try{
			return $reviewDao->getIsApprovedByItemId(self::_getItemId());
		}catch(Exception $e){
			return array();
		}
	}

	function getUser(){
		static $user;
		if(is_null($user)) $user = soyshop_get_user_object(self::_getUserId());
		return $user;
	}

	function isLoggedIn(){
		$userId = self::_getUserId();
		return (is_numeric($userId) && $userId > 0);
	}

	private function _getUserId(){
		static $userId;
		if(is_null($userId)){
			$attributes = self::_getAttributes();
			$userId = (isset($attributes["userId"])) ? (int)$attributes["userId"] : null;
		}
		return $userId;
	}

	private function _getItemId(){
		return $this->page->getItem()->getId();
	}

	private function _getAttributes(){
		static $attrs;
		if(is_null($attrs)) $attrs = MyPageLogic::getMyPage()->getAttributes();
		return $attrs;
	}

	private function _reviewDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
			$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		}
		return $dao;
	}

    function setPage($page){
    	if(!$this->page) $this->page = $page;
    }
}
