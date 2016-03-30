<?php
class CommonSitemapXmlConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		$form = SOY2HTMLFactory::createInstance("CommonSitemapXmlConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "サイトマップXMLの表示設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "common_sitemap_xml", "CommonSitemapXmlConfig");

class CommonSitemapXmlConfigFormPage extends WebPage{
	
	function CommonSitemapXmlConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
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
