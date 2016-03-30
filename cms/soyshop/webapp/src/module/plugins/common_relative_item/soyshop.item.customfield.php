<?php
/*
 */
class CommonRelativeItemField extends SOYShopItemCustomFieldBase{

	var $itemDAO;

	function doPost(SOYShop_Item $item){
		if(isset($_POST["relative_items"]) && is_array($_POST["relative_items"])){

			$array = $_POST["relative_items"];

			//空は削除
			$array = array_diff($array,array(""));

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

			try{
				$attr = $dao->get($item->getId(),"_relative_items");
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($item->getId());
				$attr->setFieldId("_relative_items");
				$dao->insert($attr);
			}

			$attr->setValue(soy2_serialize($array));
			$dao->update($attr);
		}
	}

	function getForm(SOYShop_Item $item){
		include_once(dirname(__FILE__) . "/form/RelativeItemFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("RelativeItemFormPage");
		$form->setItem($item);
		$form->setConfigObj($this);
		$form->execute();
		echo $form->getObject();
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//do nothing
		//関連商品はsiteで提供
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_relative_item","CommonRelativeItemField");
?>