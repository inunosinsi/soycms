<?php
class ItemSubtitleConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		//下記で取得しているConfig用のページのクラスファイルを読み込み、対になるHTMLファイルを出力する
		SOY2::import("module.plugins.common_item_subtitle.config.ItemSubtitleConfigPage");
		$form = SOY2HTMLFactory::createInstance("ItemSubtitleConfigPage");
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品名サブタイトルプラグインの使い方";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_item_subtitle", "ItemSubtitleConfig");
