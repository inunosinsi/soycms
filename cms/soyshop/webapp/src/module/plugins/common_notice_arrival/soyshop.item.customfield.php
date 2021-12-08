<?php

class CommonNoticeArrivalCustomField extends SOYShopItemCustomFieldBase{

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//マイページにログインしているか？ ログインしていれば0より大きい整数になる
		$loggedInUserId = MyPageLogic::getMyPage()->getUserId();

		//該当する商品が入荷通知希望に登録されているか？
		$isRegistered = (is_numeric($item->getId()) && $item->getStock() <= 0) ? self::_logic()->checkRegistered((int)$item->getId(), $loggedInUserId) : false;

		$htmlObj->addActionLink("notice_arrival_register_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => "?notice=" . $item->getId() ."&a=add",
			"attr:onclick" => "return confirm('入荷通知登録をしますか？');",
			"visible" => (!$isRegistered)
		));

		//マイページの時だけ使用予定
		$htmlObj->addModel("is_notice", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (is_numeric($item->getId()) && $loggedInUserId > 0 && SOYSHOP_MYPAGE_MODE) ? self::_logic()->checkSended($item->getId(), $loggedInUserId) : false
		));
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_notice_arrival", "CommonNoticeArrivalCustomField");
