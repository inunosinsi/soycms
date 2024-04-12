<?php
class BulkPageRemoveConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.bulk_page_remove_plugin.config.BRPPage");
		$form = SOY2HTMLFactory::createInstance("BRPPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ページとテンプレートの一括削除の操作";
	}
}
SOYShopPlugin::extension("soyshop.config", "bulk_page_remove_plugin", "BulkPageRemoveConfig");
