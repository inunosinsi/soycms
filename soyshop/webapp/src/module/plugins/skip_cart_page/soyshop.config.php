<?php
class SkipCartPageConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.skip_cart_page.config.SkipCartPageConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("SkipCartPageConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "カートページスキッププラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "skip_cart_page", "SkipCartPageConfig");
