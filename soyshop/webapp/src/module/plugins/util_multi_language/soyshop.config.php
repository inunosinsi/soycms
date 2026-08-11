<?php
class UtilMultiLanguageConfig extends SOYShopConfigPageBase{
	
	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["language"])){
			if(isset($_GET["item_id"])){
				include_once(dirname(__FILE__)  . "/config/CustomConfigFormPage.class.php");
				$form = SOY2HTMLFactory::createInstance("CustomConfigFormPage");
			}else if(isset($_GET["category_id"])){
				include_once(dirname(__FILE__)  . "/config/CustomCategoryConfigFormPage.class.php");
				$form = SOY2HTMLFactory::createInstance("CustomCategoryConfigFormPage");
			}			
		}else{
			include_once(dirname(__FILE__)  . "/config/UtilMultiLanguageConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("UtilMultiLanguageConfigFormPage");
		}
			
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["language"]) && isset($_GET["item_id"])){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$name = soyshop_get_item_object((int)$_GET["item_id"])->getName();
			if(is_string($name)){
				return $name  . " - "  . UtilMultiLanguageUtil::getLanguageText($_GET["language"]);
			}else{
				return null;
			}
		}else{
			return "多言語サイト設定";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "util_multi_language", "UtilMultiLanguageConfig");