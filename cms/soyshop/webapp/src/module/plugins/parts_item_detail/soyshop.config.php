<?php
class ItemDetailConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("ItemDetailConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品詳細設定プラグインの設定方法";
	}
}
SOYShopPlugin::extension("soyshop.config","parts_item_detail","ItemDetailConfig");


class ItemDetailConfigFormPage extends WebPage{
	
	private $config;
	
	function ItemDetailConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
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