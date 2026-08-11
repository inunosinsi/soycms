<?php
include_once(dirname(__FILE__) . "/common.php");
class ButtonSocialConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__). "/config/ButtonSocialConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("ButtonSocialConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "ソーシャルボタンの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "button_social", "ButtonSocialConfig");
?>