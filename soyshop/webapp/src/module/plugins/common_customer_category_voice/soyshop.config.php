<?php
class CommonCustomerCategoryVoiceConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		$html = file_get_contents(dirname(__FILE__) . "/soyshop.config.html");
		return $html;
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "お客様の声の設定方法";
	}

}
SOYShopPlugin::extension("soyshop.config","common_customer_category_voice","CommonCustomerCategoryVoiceConfig");
?>