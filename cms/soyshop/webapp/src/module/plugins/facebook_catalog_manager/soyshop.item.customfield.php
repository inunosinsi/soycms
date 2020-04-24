<?php
SOY2::import("module.plugins.facebook_catalog_manager.util.FbCatalogManagerUtil");
class FacebookCatalogManagerItemCustomField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "facebook_catalog_manager";

	function doPost(SOYShop_Item $item){
		if(isset($_POST["FbCatalogManager"])){
			$fb = $_POST["FbCatalogManager"];
			$isExhibition = (isset($fb[FbCatalogManagerUtil::FIELD_ID_EXHIBITATION]) && $fb[FbCatalogManagerUtil::FIELD_ID_EXHIBITATION] == 1) ? 1 : 0;
			FbCatalogManagerUtil::save($item->getId(), FbCatalogManagerUtil::FIELD_ID_EXHIBITATION, $isExhibition);

			$toxonomy = (isset($fb[FbCatalogManagerUtil::FIELD_ID_TAXONOMY])) ? soy2_serialize($fb[FbCatalogManagerUtil::FIELD_ID_TAXONOMY]) : null;
			FbCatalogManagerUtil::save($item->getId(), FbCatalogManagerUtil::FIELD_ID_TAXONOMY, $toxonomy);

			$itemCnf = (isset($fb[FbCatalogManagerUtil::FIELD_ID_ITEM_INFO])) ? soy2_serialize($fb[FbCatalogManagerUtil::FIELD_ID_ITEM_INFO]) : "";
			FbCatalogManagerUtil::save($item->getId(), FbCatalogManagerUtil::FIELD_ID_ITEM_INFO, $itemCnf);
		}else{
			self::_delete($item->getId());
		}
	}

	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.facebook_catalog_manager.form.FbCatalogCustomfieldFormPage");
		$form = SOY2HTMLFactory::createInstance("FbCatalogCustomfieldFormPage");
		$form->setItemId($item->getId());
		$form->execute();
		return $form->getObject();
	}

	function onOutput($htmlObj, SOYShop_Item $item){}
	function onDelete($itemid){
		self::_delete($itemId);
	}

	private function _delete($itemId){
		FbCatalogManagerUtil::delete($itemId, FbCatalogManagerUtil::FIELD_ID_EXHIBITATION);
		FbCatalogManagerUtil::delete($itemId, FbCatalogManagerUtil::FIELD_ID_TAXONOMY);
		FbCatalogManagerUtil::delete($itemId, FbCatalogManagerUtil::FIELD_ID_ITEM_INFO);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "facebook_catalog_manager", "FacebookCatalogManagerItemCustomField");
