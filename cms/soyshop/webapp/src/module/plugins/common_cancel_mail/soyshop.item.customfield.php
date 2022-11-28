<?php
/*
 */
class CancelMailCustomField extends SOYShopItemCustomFieldBase{

    function doPost(SOYShop_Item $item){
		$v = (isset($_POST["CancelMail"]) && is_string($_POST["CancelMail"])) ? trim($_POST["CancelMail"]) : null;
		SOY2::import("module.plugins.common_cancel_mail.util.CancelMailUtil");
	    $attr = soyshop_get_item_attribute_object($item->getId(), CancelMailUtil::PLUGIN_ID . "_" . CancelMailUtil::MODE_EMAIL);
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
    }

    function getForm(SOYShop_Item $item){
        SOY2::import("module.plugins.common_cancel_mail.form.CancelMailFormPage");
        $form = SOY2HTMLFactory::createInstance("CancelMailFormPage");
        $form->setConfigObj($this);
        $form->setItemId($item->getId());
        $form->execute();
        return $form->getObject();
    }

    function onDelete(int $itemId){
        SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($itemId);
    }
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_cancel_mail", "CancelMailCustomField");
