<?php
class CommonMailbuilderConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		//下記で取得しているConfig用のページのクラスファイルを読み込み、対になるHTMLファイルを出力する
		include_once(dirname(__FILE__) . "/config/CommonMailbuilderConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonMailbuilderConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "メールビルダーの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_mailbuilder", "CommonMailbuilderConfig");
