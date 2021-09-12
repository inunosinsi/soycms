<?php
/*
 */
class CommonRelativeItemField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
		$arr = (isset($_POST["relative_items"]) && is_array($_POST["relative_items"])) ? array_diff($_POST["relative_items"], array("")) : array();
		RelativeItemUtil::save($item->getId(), $arr);	//空は削除
	}

	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.common_relative_item.form.RelativeItemFormPage");
		$form = SOY2HTMLFactory::createInstance("RelativeItemFormPage");
		$form->setItemId($item->getId());
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//do nothing
		//関連商品はsiteで提供
	}

	function onDelete($id){
		try{
			self::_dao()->deleteByItemId($id);
		}catch(Exception $e){
			//
		}
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_relative_item","CommonRelativeItemField");
