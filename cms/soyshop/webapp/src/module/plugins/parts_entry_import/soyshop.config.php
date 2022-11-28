<?php
/**
 * プラグイン 詳細設定画面
 */
class EntryImportConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.parts_entry_import.config.EntryImportConfigPage");
		$form = SOY2HTMLFactory::createInstance("EntryImportConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ブログ記事表示設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "parts_entry_import", "EntryImportConfig");
