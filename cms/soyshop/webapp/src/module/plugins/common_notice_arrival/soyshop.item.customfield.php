<?php

class CommonNoticeArrivalCustomField extends SOYShopItemCustomFieldBase{

	private $noticeLogic;
	private $isLoggedIn;
	private $checkRegister;
	private $checkStock;
	private $checkNotice;	//通知済みかどうか

	function doPost(SOYShop_Item $item){
			
	}

	function getForm(SOYShop_Item $item){
		
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$this->prepare($item->getId());
		
		$htmlObj->addActionLink("notice_arrival_register_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "?notice=" . $item->getId() ."&a=add",
			"onclick" => "return confirm('入荷通知登録をしますか？');",
			"visible" => ($this->isLoggedIn && $this->checkStock && !$this->checkRegister)
		));
		
		
		//マイページの時だけ使用予定
		$htmlObj->addModel("is_notice", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($this->checkNotice)
		));
	}
	
	function prepare($itemId){
		//１ページで一回だけ調べる
		if(!$this->noticeLogic && isset($itemId)){
			$this->noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
			$this->isLoggedIn = $this->noticeLogic->checkLogin();
		}
		
		//商品ごとに調べる
		if($this->isLoggedIn){
			$this->checkRegister = $this->noticeLogic->checkRegisterNotice($itemId);
			$this->checkStock = $this->noticeLogic->checkStock($itemId);
			
			//マイページのみ動かす
			if(isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] == "/" . soyshop_get_mypage_uri() . "/notice"){
				$this->checkNotice = $this->noticeLogic->checkNotice($itemId);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_notice_arrival", "CommonNoticeArrivalCustomField");
?>