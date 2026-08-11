<?php
/*
 */
class OrderMailInsertTemplateItemCustomField extends SOYShopItemCustomFieldBase{

	function __construct(){
		SOY2::import("module.plugins.order_mail_insert_template.util.InsertStringTemplateUtil");
	}

    function doPost(SOYShop_Item $item){
		if(isset($_POST["MailTemplate"])){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

			if(strlen($_POST["MailTemplate"])){
				try{
					$obj = $dao->get($item->getId(), InsertStringTemplateUtil::FIELD_ID);
				}catch(Exception $e){
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId(InsertStringTemplateUtil::FIELD_ID);
				}

				$obj->setValue($_POST["MailTemplate"]);
				try{
					$dao->insert($obj);
				}catch(Exception $e){
					try{
						$dao->update($obj);
					}catch(Exception $e){
						//
					}
				}
			}else{	//削除
				try{
					$dao->delete($item->getId(), InsertStringTemplateUtil::FIELD_ID);
				}catch(Exception $e){
					//
				}
			}
		}
    }

    function getForm(SOYShop_Item $item){
        SOY2::import("module.plugins.order_mail_insert_template.form.MailInsertTemplateFormPage");
        $form = SOY2HTMLFactory::createInstance("MailInsertTemplateFormPage");
        $form->setItemId($item->getId());
        $form->execute();
        return $form->getObject();
    }

    /**
     * onOutput
     */
    function onOutput($htmlObj, SOYShop_Item $item){}

    function onDelete(int $itemId){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "order_mail_insert_template", "OrderMailInsertTemplateItemCustomField");
