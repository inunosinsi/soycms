<?php
/*
 */
class CancelMailCustomField extends SOYShopItemCustomFieldBase{

    function doPost(SOYShop_Item $item){

        SOY2::import("module.plugins.common_cancel_mail.util.CancelMailUtil");
        $attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		$mode = CancelMailUtil::MODE_EMAIL;

        try{
            $attr = $attrDao->get($item->getId(), CancelMailUtil::PLUGIN_ID . "_" . $mode);
        }catch(Exception $e){
            $attr = new SOYShop_ItemAttribute();
            $attr->setItemId($item->getId());
            $attr->setFieldId(CancelMailUtil::PLUGIN_ID . "_" . $mode);
        }

		$attr->setValue(trim($_POST["CancelMail"]));

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
        SOY2::import("module.plugins.common_cancel_mail.form.CancelMailFormPage");
        $form = SOY2HTMLFactory::createInstance("CancelMailFormPage");
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

SOYShopPlugin::extension("soyshop.item.customfield", "common_cancel_mail", "CancelMailCustomField");
