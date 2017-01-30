<?php
class SimpleNewsConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		if(isset($_POST["news"]) && is_array($_POST["news"])){
			$news = array_values($_POST["news"]);
			SOYShop_DataSets::put("plugin.simple_news",$news);
			$this->redirect("updated");
		}
		
		if(isset($_POST["update"]) && !array_key_exists("news", $_POST)){
			SOYShop_DataSets::put("plugin.simple_news", null);
			$this->redirect("updated");
		}

		$news = SOYShop_DataSets::get("plugin.simple_news", array());
		if(!is_array($news))$news = array();


		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "新着情報の設定";
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールの説明文を表示する
	 */
	function getConfigPageDescription(){
		return "新着情報を設定します。";
	}

}
SOYShopPlugin::extension("soyshop.config", "common_simple_news", "SimpleNewsConfig");
?>