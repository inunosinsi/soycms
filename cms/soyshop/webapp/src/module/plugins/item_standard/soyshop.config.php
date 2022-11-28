<?php
class ItemStandardConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["item_id"])){
			include_once(dirname(__FILE__) . "/config/SettingStandardPage.class.php");
			$form = SOY2HTMLFactory::createInstance("SettingStandardPage");
		}elseif(isset($_GET["collective"])){
			include_once(dirname(__FILE__) . "/config/collective/SettingPage.class.php");
			$form = SOY2HTMLFactory::createInstance("SettingPage");
		}else{
			include_once(dirname(__FILE__) . "/config/ItemStandardConfigPage.class.php");
			$form = SOY2HTMLFactory::createInstance("ItemStandardConfigPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品規格プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "item_standard", "ItemStandardConfig");
