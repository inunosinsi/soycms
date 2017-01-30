<?php
class CommonNoticeArrivalConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__) . "/config/NoticeArrivalConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("NoticeArrivalConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "入荷通知設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "common_notice_arrival", "CommonNoticeArrivalConfig");
?>