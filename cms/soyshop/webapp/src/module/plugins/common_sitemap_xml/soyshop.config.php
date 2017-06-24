<?php
class CommonSitemapXmlConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.common_sitemap_xml.config.SitemapXMLConfigPage");
		$form = SOY2HTMLFactory::createInstance("SitemapXMLConfigPage");
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
