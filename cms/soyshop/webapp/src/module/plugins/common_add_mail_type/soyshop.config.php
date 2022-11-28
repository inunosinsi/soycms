<?php
class CommonAddMailTypeConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["remove"])){
			SOY2::import("module.plugins.common_add_mail_type.config.AddMailTypeRemovePage");
			$form = SOY2HTMLFactory::createInstance("AddMailTypeRemovePage");
		}else{
			SOY2::import("module.plugins.common_add_mail_type.config.AddMailTypeConfigPage");
			$form = SOY2HTMLFactory::createInstance("AddMailTypeConfigPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "メール送信種類追加プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_add_mail_type", "CommonAddMailTypeConfig");
