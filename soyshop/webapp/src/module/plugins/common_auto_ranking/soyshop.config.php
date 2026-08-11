<?php
class CommonAutoRankingConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/AutoRankingConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("AutoRankingConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "自動売上ランキングの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_auto_ranking", "CommonAutoRankingConfig");
?>