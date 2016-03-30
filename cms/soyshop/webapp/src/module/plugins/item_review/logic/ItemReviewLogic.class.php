<?php
SOY2::import("module.plugins.item_review.common.ItemReviewCommon");
SOY2::imports("module.plugins.item_review.domain.*");
class ItemReviewLogic extends SOY2LogicBase{

	private $page;
	private $userDao;
	private $reviewDao;
	
	function ItemReviewLogic(){
		$this->reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}

	/**
	 * 投稿されたレビューを登録する
	 * @param array
	 */
	function registerReview($array){		
		$review = SOY2::cast("SOYShop_ItemReview", (object)$array);
		
		$config = ItemReviewCommon::getConfig();
		
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
		if(!is_null($review) && isset($config["point"]) && (int)$config["point"] > 0 && (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_point_base"))){
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
		try{
			return $this->reviewDao->getIsApprovedByItemId(self::getItemId());
		}catch(Exception $e){
			return array();
		}
	}

	function getUser(){
		try{
			return $this->userDao->getById(self::getUserId());
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	
	function isLoggedin(){
		$attributes = self::getAttributes();
		return (isset($attributes["userId"]));
	}
	
	private function getUserId(){
		$attributes = $this->getAttributes();
		return (isset($attributes["userId"])) ? $attributes["userId"] : null;
	}
	
	private function getItemId(){
		return $this->page->getItem()->getId();
	}
	
	private function getAttributes(){
		return MyPageLogic::getMyPage()->getAttributes();
	}
    
    function getPage(){
    	return $this->page;
    }
    
    function setPage($page){
    	if(!$this->page) $this->page = $page;
    }
}
?>