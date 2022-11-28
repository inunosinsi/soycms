<?php
/*
 */
class AddMailAddressEachItemCustomField extends SOYShopItemCustomFieldBase{

    function doPost(SOYShop_Item $item){
		SOY2::import("module.plugins.add_mailaddress_each_item.util.AddMailAddressEachItemUtil");
		$mode = AddMailAddressEachItemUtil::MODE_EMAIL;
		$attr = soyshop_get_item_attribute_object($item->getId(), AddMailAddressEachItemUtil::PLUGIN_ID . "_" . $mode);
        $attr->setValue(trim($_POST["AddMailAddress"]));
		soyshop_save_item_attribute_object($attr);
    }

    function getForm(SOYShop_Item $item){
        SOY2::import("module.plugins.add_mailaddress_each_item.form.AddMailAddressEachItemFormPage");
        $form = SOY2HTMLFactory::createInstance("AddMailAddressEachItemFormPage");
        $form->setConfigObj($this);
        $form->setItemId((int)$item->getId());
        $form->execute();
        return $form->getObject();
    }

    /**
     * onOutput
     */
    function onOutput($htmlObj, SOYShop_Item $item){}

    function onDelete(int $itemId){
        SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($itemId);
    }
}

SOYShopPlugin::extension("soyshop.item.customfield", "add_mailaddress_each_item", "AddMailAddressEachItemCustomField");
