<?php
/**
 * プラグイン 管理画面
 */
class BonusDownloadConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		SOY2::import("module.plugins.bonus_download.util.BonusDownloadConfigUtil");
		SOY2::import("module.plugins.bonus_download.util.BonusDownloadConditionUtil");
		SOY2::import("module.plugins.bonus_download.logic.BonusDownloadFileLogic");
		
		include_once(dirname(__FILE__) . "/config_form/BonusdownloadConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("BonusdownloadConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	/**
	 * h2タグ
	 * @return string
	 */
	function getConfigPageTitle(){
		return "購入特典ダウンロード";
	}
	
}
SOYShopPlugin::extension("soyshop.config", "bonus_download", "BonusDownloadConfig");
?>