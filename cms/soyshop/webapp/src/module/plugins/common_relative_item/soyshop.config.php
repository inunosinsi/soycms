<?php
class CommonRelativeItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		$form = SOY2HTMLFactory::createInstance("CommonRelativeItemConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "関連商品";
	}

}
SOYShopPlugin::extension("soyshop.config","common_relative_item","CommonRelativeItemConfig");

class CommonRelativeItemConfigFormPage extends WebPage{

	function CommonRelativeItemConfigFormPage(){

	}

	function execute(){
		WebPage::WebPage();

	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}

}

?>