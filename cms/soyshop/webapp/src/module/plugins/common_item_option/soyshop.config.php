<?php
class CommonItemOptionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		//下記で取得しているConfig用のページのクラスファイルを読み込み、対になるHTMLファイルを出力する
		if((isset($_GET["import"]))){
			//設定のインポートエクスポートの画面
			include_once(dirname(__FILE__) . "/config/CommonItemOptionExImportPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CommonItemOptionExImportPage");
		}else{
			//通常の設定画面
			include_once(dirname(__FILE__) . "/config/CommonItemOptionConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CommonItemOptionConfigFormPage");
		}
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["import"])){
			return "商品オプションの設定のインポート";
		}else{
			return "商品オプションプラグインの設定";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "common_item_option", "CommonItemOptionConfig");
?>