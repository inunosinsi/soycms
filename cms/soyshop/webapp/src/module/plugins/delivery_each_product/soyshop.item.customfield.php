<?php
/*
 */
class DeliveryEachProductCustomField extends SOYShopItemCustomFieldBase{

    function doPost(SOYShop_Item $item){

        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");
        $attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

        foreach($_POST["EachProduct"] as $mode => $v){
            try{
                $attr = $attrDao->get($item->getId(), DeliveryEachProductUtil::PLUGIN_ID . "_" . $mode);
            }catch(Exception $e){
                $attr = new SOYShop_ItemAttribute();
                $attr->setItemId($item->getId());
                $attr->setFieldId(DeliveryEachProductUtil::PLUGIN_ID . "_" . $mode);
            }

            //feeモードの場合はsoy2_serialize
            if($mode == DeliveryEachProductUtil::MODE_FEE) $v = soy2_serialize($v);

            $attr->setValue($v);

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
    }

    function getForm(SOYShop_Item $item){
        SOY2::import("module.plugins.delivery_each_product.form.DeliveryEachProductFormPage");
        $form = SOY2HTMLFactory::createInstance("DeliveryEachProductFormPage");
        $form->setConfigObj($this);
        $form->setItemId($item->getId());
        $form->execute();
        return $form->getObject();
    }

    /**
     * onOutput
     */
    function onOutput($htmlObj, SOYShop_Item $item){}

    function onDelete(int $itemId){
        try{
            SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
        }catch(Exception $e){
            //
        }
    }
}

SOYShopPlugin::extension("soyshop.item.customfield", "delivery_each_product", "DeliveryEachProductCustomField");
