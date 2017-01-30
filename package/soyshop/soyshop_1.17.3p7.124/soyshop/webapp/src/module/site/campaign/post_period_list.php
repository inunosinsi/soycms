<?php
function soyshop_post_period_list($html, $htmlObj){

	$obj = $htmlObj->create("soyshop_post_period_list", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_post_period_list", $html)
	));
	

	$campaigns = array();
	
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("campaign")){
		
		//表示件数
		$cnt = 0;
		
		if(strpos($html, "cms:count")){
			$cntTag = trim(substr($html, strpos($html, "cms:count") + 11, 5));
			$cnt = (int)trim(substr($cntTag, 0, strpos($cntTag, "\"")));
		}
		
		if($cnt === 0) $cnt = 5;
			
		SOY2::imports("module.plugins.campaign.domain.*");
		$dao = SOY2DAOFactory::create("SOYShop_CampaignDAO");
		$dao->setLimit($cnt);
		try{
			$campaigns = $dao->getWithinPostPeriodEnd();
		}catch(Exception $e){
			
		}
		
		//ログインしていない場合は、ログインのチェックをついたものだけを除く
		
		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin()){
			$list = array();
			foreach($campaigns as $cmp){
				if($cmp->getIsLoggedIn() != SOYShop_Campaign::IS_LOGGED_IN){
					$list[] = $cmp;
				}
			}
			$campaigns = $list;
		}
		
	}

	$obj->createAdd("campaign_list", "SOYShop_CampaignPostPeriodListComponent", array(
		"list" => $campaigns,
		"soy2prefix" => "block"
	));

	//キャンペーンがある時だけ表示
	if(count($campaigns) > 0){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}

class SOYShop_CampaignPostPeriodListComponent extends HTMLList {
	
	function populateItem($entity, $key, $index){

		$this->createAdd("post_period_start", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getPostPeriodStart()
		));
		
		$this->createAdd("post_period_end", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getPostPeriodEnd()
		));
		
		$this->addLabel("campaign_title", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getTitle()
		));
		
		$this->addLabel("campaign_content", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getContent()
		));
		
		$this->addLink("campaign_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_get_mypage_url() . "/campaign/" . $entity->getId()
		));
	}
}
?>