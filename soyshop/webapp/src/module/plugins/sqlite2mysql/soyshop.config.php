<?php
class SQLite2MySQLConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.sqlite2mysql.config.SQLMigrateConfigPage");
		$form = SOY2HTMLFactory::createInstance("SQLMigrateConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "SQLite→MySQL移行プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "sqlite2mysql", "SQLite2MySQLConfig");
