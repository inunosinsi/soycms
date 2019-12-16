<?php
SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
SOY2::imports("module.plugins.item_review.domain.*");
class ItemReviewLogic extends SOY2LogicBase{

	private $page;
	private $userDao;
	private $reviewDao;

	function __construct(){
		$this->reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}

	/**
	 * 投稿されたレビューを登録する
	 * @param array
	 */
	function registerReview($array){
		$review = SOY2::cast("SOYShop_ItemReview", (object)$array);

		$config = ItemReviewUtil::getConfig();

		//値を挿入する
		$review->setItemId(self::getItemId());
		$review->setUserId(self::getUserId());

		$isApproved = (!is_null($config["publish"])) ? SOYShop_ItemReview::REVIEW_IS_APPROVED : SOYShop_ItemReview::REVIEW_NO_APPROVED;
		$review->setIsApproved($isApproved);
		$review->setCreateDate(time());
		$review->setUpdateDate(time());

		try{
			$reviewId = $this->reviewDao->insert($review);
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

	function getReviews(){
		static $reviews;
		if(is_null($reviews)){
			try{
				$reviews = $this->reviewDao->getIsApprovedByItemId(self::getItemId());
			}catch(Exception $e){
				$reviews = array();
			}
		}
		return $reviews;
	}

	function getUser(){
		static $user;
		if(is_null($user)){
			try{
				$user = $this->userDao->getById(self::getUserId());
			}catch(Exception $e){
				$user = new SOYShop_User();
			}
		}
		return $user;
	}

	function isLoggedIn(){
		return (self::getUserId() > 0);
	}

	private function getUserId(){
		static $userId;
		if(is_null($userId)){
			$attributes = self::getAttributes();
			$userId = (isset($attributes["userId"])) ? (int)$attributes["userId"] : null;
		}
		return $userId;
	}

	private function getItemId(){
		return $this->page->getItem()->getId();
	}

	private function getAttributes(){
		static $attrs;
		if(is_null($attrs)) $attrs = MyPageLogic::getMyPage()->getAttributes();
		return $attrs;
	}

    function getPage(){
    	return $this->page;
    }

    function setPage($page){
    	if(!$this->page) $this->page = $page;
    }
}
