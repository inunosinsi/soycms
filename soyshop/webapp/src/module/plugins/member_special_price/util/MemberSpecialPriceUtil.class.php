<?php

class MemberSpecialPriceUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("member_special_price.config", array());
	}

	public static function saveConfig(array $values){
		return SOYShop_DataSets::put("member_special_price.config", $values);
	}

	public static function getUserCustomfieldIdList(){
		if(!class_exists("SOYShopPluginUtil") || !SOYShopPluginUtil::checkIsActive("common_user_customfield")) return array();

		SOYShopPlugin::load("soyshop.user.customfield");
		return SOYShopPlugin::invoke("soyshop.user.customfield", array("mode" => "order_csv"))->getList();
	}

	/**
	 * @param string
	 * @return string
	 */
	public static function getFieldLabelById(string $fieldId){
		static $fieldIds;
		if(is_null($fieldIds)){
			$fieldIds = self::getUserCustomfieldIdList();
		}
		return (isset($fieldIds[$fieldId])) ? "ユーザーカスタムフィールド：".$fieldIds[$fieldId] : "";
	}
}
