<?php
class TakeOverSetLinkConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.take_over_set_link.config.TakeOverSetLinkConfigPage");
		$form = SOY2HTMLFactory::createInstance("TakeOverSetLinkConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "別サイト顧客情報引継ぎ用リンク設置プラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "take_over_set_link", "TakeOverSetLinkConfig");
