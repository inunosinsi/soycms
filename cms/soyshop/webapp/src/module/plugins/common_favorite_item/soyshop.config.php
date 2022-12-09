<?php
class CommonFavoriteItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/FavoriteItemConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("FavoriteItemConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "お気に入り登録の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_favorite_item", "CommonFavoriteItemConfig");
