<?php
/*
 */
class AddMailAddressEachItemCustomField extends SOYShopItemCustomFieldBase{

    function doPost(SOYShop_Item $item){

        SOY2::import("module.plugins.add_mailaddress_each_item.util.AddMailAddressEachItemUtil");
        $attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		$mode = AddMailAddressEachItemUtil::MODE_EMAIL;

        try{
            $attr = $attrDao->get($item->getId(), AddMailAddressEachItemUtil::PLUGIN_ID . "_" . $mode);
        }catch(Exception $e){
            $attr = new SOYShop_ItemAttribute();
            $attr->setItemId($item->getId());
            $attr->setFieldId(AddMailAddressEachItemUtil::PLUGIN_ID . "_" . $mode);
        }

		$attr->setValue(trim($_POST["AddMailAddress"]));

        try{
            $attrDao->insert($attr);
        }catch(Exception $e){
            try{
                $attrDao->update($attr);
            }catch(Exception $e){
                //
            }
        }
    }

    function getForm(SOYShop_Item $item){
        SOY2::import("module.plugins.add_mailaddress_each_item.form.AddMailAddressEachItemFormPage");
        $form = SOY2HTMLFactory::createInstance("AddMailAddressEachItemFormPage");
        $form->setConfigObj($this);
        $form->setItemId($item->getId());
        $form->execute();
        return $form->getObject();
    }

    /**
     * onOutput
     */
    function onOutput($htmlObj, SOYShop_Item $item){}

    function onDelete($id){
        try{
            SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
        }catch(Exception $e){
            //
        }
    }
}

SOYShopPlugin::extension("soyshop.item.customfield", "add_mailaddress_each_item", "AddMailAddressEachItemCustomField");
