<?php
class UtilMultiLanguageConfig extends SOYShopConfigPageBase{
	
	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["language"]) && isset($_GET["item_id"])){
			include_once(dirname(__FILE__)  . "/config/CustomConfigFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("CustomConfigFormPage");
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
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			try{
				$item = $itemDao->getById($_GET["item_id"]);
				return $item->getName() . " - "  . UtilMultiLanguageUtil::getLanguageText($_GET["language"]);
			}catch(Exception $e){
				return null;
			}
		}else{
			return "多言語サイト設定";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "util_multi_language", "UtilMultiLanguageConfig");
?>