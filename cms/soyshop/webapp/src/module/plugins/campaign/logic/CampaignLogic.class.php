<?php

class CampaignLogic extends SOY2LogicBase{
	
	function CampaignLogic(){
		SOY2::imports("module.plugins.campaign.domain.*");
	}
	
	function getCampaignByIdWithinPostPeriod($campaignId){
		try{
			$campaign = self::dao()->getById($campaignId);
		}catch(Exception $e){
			return new SOYShop_Campaign();
		}
		
		//削除フラグを調べる
		if($campaign->getIsDisabled() != SOYShop_Campaign::NO_DISABLED)  return new SOYShop_Campaign();
		
		//公開状態を調べる
		if($campaign->getIsOpen() != SOYShop_Campaign::IS_OPEN) return new SOYShop_Campaign();
		
		//公開期限を調べる
		if($campaign->getPostPeriodStart() > time() || $campaign->getPostPeriodEnd() < time()) return new SOYShop_Campaign();
		
		//ログインの有無がある場合はログインしているか調べる
		if($campaign->getIsLoggedIn() == SOYShop_Campaign::IS_LOGGED_IN){
			$mypage = MyPageLogic::getMyPage();
			if(!$mypage->getIsLoggedin()) return new SOYShop_Campaign();
		}
		
		return $campaign;
	}
	
	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_CampaignDAO");
		return $dao;
	}
}
?>