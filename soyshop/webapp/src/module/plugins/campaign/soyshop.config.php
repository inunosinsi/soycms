<?php
class CampaignConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins." . $this->getModuleId() . ".util.CampaignUtil");
		if(isset($_GET["mode"]) && $_GET["mode"] == CampaignUtil::MODE_ENTRY){
			include_once(dirname(__FILE__) . "/config/CampaignEntryPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CampaignEntryPage");
		}else{
			include_once(dirname(__FILE__) . "/config/CampaignConfigPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CampaignConfigPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		SOY2::import("module.plugins." . $this->getModuleId() . ".util.CampaignUtil");
		if(isset($_GET["mode"]) && $_GET["mode"] == CampaignUtil::MODE_ENTRY){
			return "キャンペーンの登録";
		}else{
			return "キャンペーンプラグインの設定";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "campaign", "CampaignConfig");
