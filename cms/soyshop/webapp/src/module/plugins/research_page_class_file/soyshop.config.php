<?php

class ResearchPageClassFileConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.research_page_class_file.config.RPCFConfigPage");
		$form = SOY2HTMLFactory::createInstance("RPCFConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "クラスファイル調査プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "research_page_class_file", "ResearchPageClassFileConfig");
