<?php
class CustomSearchFieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.custom_search_field.component.LinkNaviAreaComponent");

		if(isset($_GET["eximport"])){
			include_once(dirname(__FILE__) . "/config/CustomSearchExImportPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomSearchExImportPage");
		//一括設定画面
		}else if(isset($_GET["collective"])){
			include_once(dirname(__FILE__) . "/config/collective/SettingPage.class.php");
			$form = SOY2HTMLFactory::createInstance("SettingPage");
		//検索の設定画面
		}else if(isset($_GET["config"])){
			include_once(dirname(__FILE__) . "/config/search/CustomSearchConfigPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomSearchConfigPage");
		//カテゴリカスタムフィールド
		}else if(isset($_GET["category"])){
			include_once(dirname(__FILE__) . "/config/category/CustomSearchFieldConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomSearchFieldConfigFormPage");
		//カスタムサーチフィールドのカスタムフィールド
		}else if(isset($_GET["custom"])){
			include_once(dirname(__FILE__) . "/config/custom/CustomFieldConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomFieldConfigFormPage");
		}else if(isset($_GET["customset"])){
			include_once(dirname(__FILE__) . "/config/custom/CustomFieldFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomFieldFormPage");
		//通常の設定画面
		}else{
			include_once(dirname(__FILE__) . "/config/CustomSearchFieldConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomSearchFieldConfigFormPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["eximport"])){
			return "カスタムフィールドの設定のインポート";
		//一括設定画面
		}else if(isset($_GET["collective"])){
			return "カスタムサーチフィールドの値の一括設定";
		//検索の設定画面
		}else if(isset($_GET["config"])){
			return "検索の設定";
		//カテゴリカスタムフィールド
		}else if(isset($_GET["category"])){
			return "カテゴリカスタムフィールド";
		//カスタムサーチフィールドのカスタムフィールド
		}else if(isset($_GET["custom"])){
			return "カスタムサーチフィールドのカスタムフィールド";
		}else if(isset($_GET["customset"])){
			return "カスタムサーチフィールドのカスタムフィールドの設定";
		//通常の設定画面
		}else{
			return "カスタムサーチフィールド";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "custom_search_field", "CustomSearchFieldConfig");
