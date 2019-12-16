<?php

class InsertStringTemplateUtil {

	const FIELD_ID = "INSERT_STRING_TEMPLATE";

	public static function getConfig(){
		//型 array(fieldId => label)
		return SOYShop_DataSets::get("order_mail_insert_template.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("order_mail_insert_template.config", $values);
	}

	public static function getTextByFieldId($fieldId){
		if($fieldId == "config") $fieldId = "conf";
		return SOYShop_DataSets::get("order_mail_insert_template." . $fieldId, null);
	}

	public static function saveTextByFieldId($fieldId, $txt){
		if($fieldId == "config") $fieldId = "conf";
		SOYShop_DataSets::put("order_mail_insert_template." . $fieldId, $txt);
	}

	public static function getMailFieldIdByItemId($itemId){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->get($itemId, self::FIELD_ID)->getValue();
		}catch(Exception $e){
			return "";
		}
	}
}
