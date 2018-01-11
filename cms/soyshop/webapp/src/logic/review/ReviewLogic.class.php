<?php

class ReviewLogic extends SOY2LogicBase{

	private $errors = array();
	private $reviewDao;
	private $reviewPointDao;
	private $pointLogic;

	function __construct(){
		SOY2::imports("module.plugins.item_review.domain.*");
		$this->reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		$this->reviewPointDao = SOY2DAOFactory::create("SOYShop_ReviewPointDAO");
		$this->pointLogic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
	}

    function update(SOYShop_ItemReview $obj){
		try{
			$this->reviewDao->update($obj);
		}catch(Exception $e){
			return;
		}

		//ポイントの加算を行うか調べる
		SOY2::import("util.SOYShopPluginUtil");
		if($obj->getIsApproved() == SOYShop_ItemReview::REVIEW_IS_APPROVED && SOYShopPluginUtil::checkIsActive("common_point_base")){
			try{
				$pObj = $this->reviewPointDao->getByReviewId($obj->getId());
			}catch(Exception $e){
				return;
			}

			$history = "レビュー投稿で" . $pObj->getPoint() . "ポイント追加";
			$this->pointLogic->insert($pObj->getPoint(), $history, $obj->getUserId());

			try{
				$this->reviewPointDao->deleteByReviewId($obj->getId());
			}catch(Exception $e){
				//
			}
		}

    }

    function delete($ids){
    	if(!is_array($ids))$ids = array($ids);

    	$this->reviewDao->begin();
    	foreach($ids as $id){
    		$this->reviewDao->delete($id);
    	}
    	$this->reviewDao->commit();
    }

    function create(SOYShop_ItemReview $obj){
//		$siteUrl = soyshop_get_site_url();
		return $this->reviewDao->insert($obj);
    }

    function getErrors() {
    	return $this->errors;
    }
    function setErrors($errors) {
    	$this->errors = $errors;
    }

	/**
	 * 公開状態を変更する
	 */
    function changeOpen($reviewIds, $status){
    	if(!is_array($reviewIds)) $reviewIds = array($reviewIds);
    	$status = (int)(boolean)$status;	//0 or 1

    	$this->reviewDao->begin();

    	foreach($reviewIds as $id){
			try{
				//$dao->updateIsApproved($id, (int)$status);
				$review = $this->reviewDao->getById($id);
				$review->setIsApproved($status);
				self::update($review);
			}catch(Exception $e){
				continue;
			}
    	}
    	$this->reviewDao->commit();
    }
}
