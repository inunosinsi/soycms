<?php

class FbCatalogManagerUtil {

	const FIELD_ID_EXHIBITATION = "fb_cat_exhibitation";
	const FIELD_ID_TAXONOMY = "fb_cat_taxonomy";
	const FIELD_ID_ITEM_INFO = "fb_item_info";

	public static function getConfig(){
		$cnf = SOYShop_DataSets::get("facebook_catalog_manager.config", array(
			"shopName" => null,
			"shopDescription" => "",
			"shippingPrice" => 0
		));

		if(is_null($cnf["shopName"])){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf["shopName"] = SOYShop_ShopConfig::load()->getShopName();
		}
		return $cnf;
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("facebook_catalog_manager.config", $values);
	}

	public static function save($itemId, $fieldId, $value){
		//削除
		if(!strlen($value) || (is_array($value) && !count($value)) || (is_numeric($value) && (int)$value === 0)){
			self::_delete($itemId, $fieldId);
		}else{
			$dao = self::_attrDao();
			$attr = self::_get($itemId, $fieldId);
			if(is_array($value)) $value = soy2_serialize($value);
			$attr->setValue($value);

			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					return false;
				}
			}
		}
		return true;
	}

	public static function get($itemId, $fieldId){
		return self::_get($itemId, $fieldId);
	}

	public static function delete($itemId, $fieldId){
		self::_delete($itemId, $fieldId);
	}

	private static function _get($itemId, $fieldId){
		try{
			return self::_attrDao()->get($itemId, $fieldId);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId($fieldId);
			return $attr;
		}
	}

	private static function _delete($itemId, $fieldId){
		try{
			self::_attrDao()->delete($itemId, $fieldId);
		}catch(Exception $e){
			//
		}
	}

	public static function getExhibitionItemInfoList(){
		$ids = self::_getExhibitionItemIdList();
		if(!count($ids)) return array();

		$sql = "SELECT * FROM soyshop_item_attribute WHERE item_id IN (" . implode(",", $ids) . ") AND (item_field_id = '" . self::FIELD_ID_TAXONOMY . "' OR item_field_id = '" . self::FIELD_ID_ITEM_INFO . "')";
		try{
			$res = self::_attrDao()->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$infos = array();
		foreach($res as $v){
			$infos[(int)$v["item_id"]][$v["item_field_id"]] = (strlen($v["item_value"])) ? soy2_unserialize($v["item_value"]) : array();
		}

		//値の設定がないものがないか？調べる
		foreach($ids as $id){
			if(!isset($infos[$id])) $infos[$id] = array();
		}

		return $infos;
	}

	private static function _getExhibitionItemIdList(){
		$sql = "SELECT item_id FROM soyshop_item_attribute WHERE item_field_id = '" . self::FIELD_ID_EXHIBITATION . "' AND item_value = '1'";
		try{
			$res = self::_attrDao()->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ids = array();
		foreach($res as $v){
			if(!isset($v["item_id"]) || !is_numeric($v["item_id"])) continue;
			$ids[] = (int)$v["item_id"];
		}

		return $ids;
	}

	public static function getConditionList(){
		return array(
			"new" => "新品",
			"refurbished" => "再生品",
			"used" => "中古",
			"used_fair" => "中古(ある程良い状態)",
			"used_good" => "中古(良い状態)",
			"used_like_new" => "中古(新品に近い)"
		);
	}

	private static function _attrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}
