<?php
class ItemListRandomConfig extends SOYShopConfigPageBase{

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
		return "商品のランダム表示の設定方法";
	}

}
SOYShopPlugin::extension("soyshop.config", "item_list_random", "ItemListRandomConfig");

?>
