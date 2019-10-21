<?php

class PartsItemDetailUtil {

	const FIELD_ID = "breadcrumb_change";
	const PARENT_FIELD_ID = "breadcrumb_change_parent";

	public static function getItemByAlias($alias){
		static $item;
		if(isset($item)) return $item;

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if(!strlen($alias)) {
			$item = self::_getNoExistItem();
			return $item;
		}

		try{
			$item = $dao->getByCode($alias);
		}catch(Exception $e){
			try{
				$item = $dao->getByAlias($alias);
			}catch(Exception $e){
				if(!strpos($alias, ".html")){
					try{
						$item = $dao->getByAlias($alias . ".html");
					}catch(Exception $e){
						$item = self::_getNoExistItem();
						return $item;
					}
				}
			}
		}

		if(is_null($item->getId())) return $item;

		//削除されていないか？
		if($item->getIsDisabled() == SOYShop_Item::IS_DISABLED) {
			$item = self::_getNoExistItem();
			return $item;
		}

		//公開されていないか？
		if($item->getIsOpen() == SOYShop_Item::NO_OPEN) {
			$item = self::_getNoExistItem();
			return $item;
		}

		//公開期限外であるか？
		if($item->getOpenPeriodStart() > SOY2_NOW || $item->getOpenPeriodEnd() < SOY2_NOW) {
			$item = self::_getNoExistItem();
			return $item;
		}

		return $item;
	}

	public static function getAttr($itemId, $fieldId){

		try{
			$attr = self::_dao()->get($itemId, $fieldId);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId($fieldId);
		}
		return $attr;
	}

	public static function saveAttr(SOYShop_ItemAttribute $attr, $fieldId){
		if(is_numeric($attr->getValue()) && $attr->getValue() > 0){
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
				self::dao()->delete($attr->getItemId(), $fieldId);
			}catch(Exception $e){

			}
		}
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}

	private static function _getNoExistItem($name="商品が存在していません"){
		$item = new SOYShop_Item();
		$item->setName($name);
		$item->setIsOpen(SOYShop_Item::IS_OPEN);
		return $item;
	}
}
