<?php
class AutoCompletionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins." . $this->getModuleId() . ".config.AutoCompletionConfigPage");
		$form = SOY2HTMLFactory::createInstance("AutoCompletionConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品名検索入力補完プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "auto_completion_item_name", "AutoCompletionConfig");
