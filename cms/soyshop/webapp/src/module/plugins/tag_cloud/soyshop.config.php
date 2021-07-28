<?php
class TagCloudConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.tag_cloud.config.TCConfigPage");
		$form = SOY2HTMLFactory::createInstance("TCConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "タグクラウドの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "tag_cloud", "TagCloudConfig");
