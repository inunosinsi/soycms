<?php
class InquiryOnMypageConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.inquiry_on_mypage.config.InquiryConfigPage");
		$form = SOY2HTMLFactory::createInstance("InquiryConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "マイページ用お問い合わせフォームの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "inquiry_on_mypage", "InquiryOnMypageConfig");
