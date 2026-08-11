<?php
class UserCustomSearchFieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["collective"])){
			include_once(dirname(__FILE__) . "/config/collective/SettingPage.class.php");
			$form = SOY2HTMLFactory::createInstance("SettingPage");
		//検索の設定画面
		}else{
			include_once(dirname(__FILE__) . "/config/UserCustomSearchFieldConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("UserCustomSearchFieldConfigFormPage");	
		}
			
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ユーザーカスタムサーチフィールド";
	}
}
SOYShopPlugin::extension("soyshop.config", "user_custom_search_field", "UserCustomSearchFieldConfig");
?>