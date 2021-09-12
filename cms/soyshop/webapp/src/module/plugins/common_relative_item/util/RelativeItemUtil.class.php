<?php

class RelativeItemUtil{

	const FIELD_ID = "_relative_items";

	public static function getConfig(){
		return SOYShop_DataSets::get("relative_item.config", array(
			"defaultSort" => "name",
			"isReverse" => 0
		));
	}

	public static function saveConfig(array $values){
		SOYShop_DataSets::put("relative_item.config", $values);
	}

	public static function getCodesByItemId(int $itemId){
		$v = self::_attr($itemId)->getValue();
		if(!strlen($v)) return array();

		$codes = soy2_unserialize($v);
		return (is_array($codes)) ? $codes : array();
	}

	public static function save(int $itemId, array $arr){
		if(count($arr)){
			$attr = self::_attr($itemId);
			$attr->setValue(soy2_serialize($arr));

			try{
				self::_dao()->insert($attr);
			}catch(Exception $e){
				try{
					self::_dao()->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				self::_dao()->delete($itemId, self::FIELD_ID);
			}catch(Exception $e){
				//
			}
		}
	}

	private static function _attr(int $itemId){
		try{
			return self::_dao()->get($itemId, self::FIELD_ID);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::FIELD_ID);
			return $attr;
		}
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}
