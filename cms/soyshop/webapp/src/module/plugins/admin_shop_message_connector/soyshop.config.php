<?php
class AdminShopMessageConnectorConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		$form = SOY2HTMLFactory::createInstance("AdminShopMessageConnectorConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "SOY Message連携プラグイン";
	}
	
}
SOYShopPlugin::extension("soyshop.config","admin_shop_message_connector","AdminShopMessageConnectorConfig");

class AdminShopMessageConnectorConfigFormPage extends WebPage{
		
	function AdminShopMessageConnectorConfigFormPage(){

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