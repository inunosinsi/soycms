<?php
SOY2::imports("module.plugins.item_review.domain.*");
class ReviewLogic extends SOY2LogicBase{

	private $errors = array();

    function update(SOYShop_ItemReview $obj){
		$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		try{
			$dao->update($obj);
		}catch(Exception $e){
			return;
		}
		
		//ポイントの加算を行うか調べる
		if($obj->getIsApproved() == SOYShop_ItemReview::REVIEW_IS_APPROVED && (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_point_base"))){
			SOY2::imports("module.plugins.item_review.domain.*");
			$pDao = SOY2DAOFactory::create("SOYShop_ReviewPointDAO");
			try{
				$pObj = $pDao->getByReviewId($obj->getId());
			}catch(Exception $e){
				var_dump($e);
				return;
			}
			
			$history = "レビュー投稿で" . $pObj->getPoint() . "ポイント追加";
			SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->insert($pObj->getPoint(), $history, $obj->getUserId());
			
			try{
				$pDao->deleteByReviewId($obj->getId());
			}catch(Exception $e){
				//
			}
		}
		
    }

    function delete($ids){
    	if(!is_array($ids))$ids = array($ids);

    	$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

    	$dao->begin();
    	foreach($ids as $id){
    		$dao->delete($id);
    	}
    	$dao->commit();
    }

    function create(SOYShop_ItemReview $obj){
		$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

		$siteUrl = soyshop_get_site_url();

		return $dao->insert($obj);
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

    	$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
    	$dao->begin();

    	foreach($reviewIds as $id){
			$dao->updateIsApproved($id, (int)$status);
    	}
    	$dao->commit();
    }
}
?>