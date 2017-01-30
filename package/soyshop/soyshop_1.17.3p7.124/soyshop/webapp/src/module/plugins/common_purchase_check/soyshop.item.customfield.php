<?php
class CommonPurchaseCheckCustomField extends SOYShopItemCustomFieldBase{
	
	private $checkLogic;
	private $isLoggedIn;
	
	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$this->prepare();
		
		$purchasedFlag = false;
		
		//商品情報がある時だけ購入済みであるかを調べる
		if(!is_null($item->getId()) && $this->isLoggedIn){
			$purchasedFlag = $this->checkLogic->checkPurchased($item->getId());
		}
		
		$htmlObj->addModel("is_purchased", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($purchasedFlag === true)
		));
		
		$htmlObj->addModel("no_purchased", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($purchasedFlag === false)
		));
	}
	
	function prepare(){
		if(is_null($this->isLoggedIn)){
			//ログインチェック
			$mypage = MyPageLogic::getMyPage();
			$this->isLoggedIn = $mypage->getIsLoggedin();
			if($this->isLoggedIn){
				$userId = $mypage->getUserId();
				$this->checkLogic = SOY2Logic::createInstance("module.plugins.common_purchase_check.logic.PurchasedCheckLogic", array("userId" => $userId));
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "common_purchase_check", "CommonPurchaseCheckCustomField");
?>