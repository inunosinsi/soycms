<?php
class ItemListCategoryCustomfield extends SOYShopItemCustomFieldBase{

	const MODULE_ID = "item_list_category_customfield";

	function doPost(SOYShop_Item $item){
		
		if(isset($_POST["ItemListCategoryCustomfield"])){
			$itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			try{
				$itemAttributeDao->delete($item->getId(), self::MODULE_ID);
			}catch(Exception $e){
				//
			}
			
			$obj = new SOYShop_ItemAttribute();
			$obj->setFieldId(self::MODULE_ID);
			$obj->setItemId($item->getId());
			$obj->setValue($_POST["ItemListCategoryCustomfield"]);
			
			try{
				$itemAttributeDao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	function getForm(SOYShop_Item $item){
		include_once(dirname(__FILE__) . "/form/ItemListCategoryCustomfieldDetailFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("ItemListCategoryCustomfieldDetailFormPage");
		$form->setConfigObj($this);
		$form->setItemId($item->getId());
		$form->setModuleId(self::MODULE_ID);
		$form->execute();
		return $form->getObject();
	}

	function onDelete($id){
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "item_list_category_customfield", "ItemListCategoryCustomfield");
?>