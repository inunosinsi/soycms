<?php
class ItemInfoEditConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.item_info_edit.config.EditButtonSetPage");
		$form = SOY2HTMLFactory::createInstance("EditButtonSetPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "商品情報編集ボタン設置プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "item_info_edit", "ItemInfoEditConfig");
