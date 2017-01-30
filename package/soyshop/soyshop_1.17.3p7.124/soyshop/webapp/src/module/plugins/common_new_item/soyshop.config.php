<?php
class CommonNewItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.common_new_item.config.NewItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("NewItemConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "新着商品設定";
	}

}
SOYShopPlugin::extension("soyshop.config","common_new_item","CommonNewItemConfig");
?>
