<?php
class TagCloudAdminList extends SOYShopAdminListBase{

	function getTabName(){
		return (SOYShopPluginUtil::checkIsActive("util_multi_language")) ? "タグクラウド" : "";
	}

	function getTitle(){
		return (SOYShopPluginUtil::checkIsActive("util_multi_language")) ? "タグクラウド多言語化設定" : "";;
	}

	function getContent(){
		if(!SOYShopPluginUtil::checkIsActive("util_multi_language")) {
			SOY2PageController::jump();
		}
		$args = SOY2PageController::getArguments();
		$itemId = (isset($args[1]) && is_numeric($args[1])) ? (int)$args[1] : 0;

		if($itemId > 0 && is_null(soyshop_get_item_object($itemId)->getId())){
			SOY2PageController::jump();
		}
		
		SOY2::import("module.plugins.tag_cloud.page.admin.TagCloudMultiLanguagePage");
		$form = SOY2HTMLFactory::createInstance("TagCloudMultiLanguagePage");
		$form->setItemId($itemId);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "tag_cloud", "TagCloudAdminList");
