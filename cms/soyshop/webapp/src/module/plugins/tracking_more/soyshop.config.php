<?php
class TrackingMoreConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.tracking_more.config.TrackingMoreConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("TrackingMoreConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "Trackingmoreの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "tracking_more", "TrackingMoreConfig");
