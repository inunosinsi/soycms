<?php

class RedirectLogic extends SOY2LogicBase{
	
	const PLUGIN_ID = "SOYShopLoginCheck";
	
	private $loginPageUrl;
	private $configPerBlog;
	
	function RedirectLogic(){}
	
	function redirectLoginForm($page, $mode){
		
		$redirectFlag = false;
		
		//念のため、ログインフォームのURLが取得できているかを確認
		if(isset($this->loginPageUrl) && strlen($this->loginPageUrl) > 0){
			$pageType = $page->getPageType();
				
			//ブログページの場合
			if($pageType == Page::PAGE_TYPE_BLOG){
				//ブログのタイプ毎に設定内容を調べる
				if($this->configPerBlog[$page->getId()][$mode]){
					$redirectFlag = true;
				}
			//ブログ以外のページはtrue
			}else{
				$redirectFlag = true;
			}
			
			if($redirectFlag){
				$this->execRedirect();
			}
		}
	}
	
	function redirectItemDetailPage($entryId, $siteId){
		$attributeDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		try{
			$obj = $attributeDao->get($entryId, self::PLUGIN_ID);
		}catch(Exception $e){
			$obj = new EntryAttribute();
		}
		
		$itemCodeValues = $obj->getValue();
		if(isset($itemCodeValues) && strlen($itemCodeValues) > 0){
			$itemLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.ItemLogic", array("siteId" => $siteId));
			
			$itemCodes = explode(",", $itemCodeValues);
			for($i = 0; $i < count($itemCodes); $i++){
				if(strlen($itemCodes[$i]) === 0){
					unset($itemCodes[$i]);
				}
			}
			
			if(count($itemCodes) > 0){
				try{
					$typeObj = $attributeDao->get($entryId, self::PLUGIN_ID . "Type");
				}catch(Exception $e){
					$typeObj = new EntryAttribute();
				}
				
				//指定した商品すべて購入していることが条件
				if($typeObj->getValue() == "and"){
					foreach($itemCodes as $itemCode){
						//商品コードを一つずつ調べる
						if(!$itemLogic->checkPurchasedSingle($itemCode)){
							$url = $itemLogic->getItemDetailPageUrl($itemCode);
							header("Location:" . $url);
							exit;
						}
					}
				}else{
					//falseの場合は購入したことがない商品
					if(!$itemLogic->checkPurchased($itemCodes)){
						$itemCode = array_shift($itemCodes);
						$url = $itemLogic->getItemDetailPageUrl($itemCode);
						header("Location:" . $url);
						exit;
					}	
				}
			}
		}
	}
	
	function execRedirect(){
		header("Location:" . $this->loginPageUrl);
	}
		
	function setLoginPageUrl($loginPageUrl){
		$this->loginPageUrl = $loginPageUrl;
	}
	
	function setConfigPerBlog($configPerBlog){
		$this->configPerBlog = $configPerBlog;
	}
}
?>