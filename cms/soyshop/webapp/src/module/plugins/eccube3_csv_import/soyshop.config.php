<?php
class ECCUBE3CSVImportConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.eccube3_csv_import.config.ECCUBE3CSVImportConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("ECCUBE3CSVImportConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "EC CUBE3 CSVインポートプラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config","eccube3_csv_import","ECCube3CSVImportConfig");
