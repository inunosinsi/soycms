<?php
class SOYShopSortButtonConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__) . "/config/SortButtonConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SortButtonConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "ソートボタンの設定方法";
	}

}
SOYShopPlugin::extension("soyshop.config", "common_sort_button", "SOYShopSortButtonConfig");
?>