<?php

//ハッシュテーブル用のハッシュ値を作成する
function soyshop_generate_hash_value(string $str, int $length=12){
	$hash = md5($str);
	return substr($hash, 0, $length);
}

function soyshop_get_hash_table_types(){
	static $types;
	if(is_null($types)) $types = array("item", "item_attribute", "item_children", "category", "category_attribute", "user", "user_attribute", "page", "order", "order_attribute", "order_date_attribute", "plugin");
	return $types;
}

//ハッシュ値を記録したテーブルを用いてインデックスを検索する
function soyshop_get_hash_index(string $str, string $fnName){
	static $tables;
	if(is_null($tables)){
		$tables = array();
		for($i = 0; $i < count(soyshop_get_hash_table_types()); $i++){
			$tables[] = array();
		}
	}
	$idx = array_search(str_replace(array("soyshop_get_", "_object"), "", $fnName), soyshop_get_hash_table_types());
	$hash = soyshop_generate_hash_value($str);
	if(!count($tables[$idx]) || !is_numeric(array_search($hash, $tables[$idx]))) $tables[$idx][] = $hash;
	return array_search($hash, $tables[$idx]);
}

/**
 * 各attribute値を$dataTypeに従って変換する
 * dataType : string, int, bool
 */
function soyshop_get_attribute_value($v=null, $dataType=""){
	switch($dataType){
		case "string":
			return (is_string($v)) ? $v : "";
			break;
		case "int":
			if(is_numeric($v)) return (int)$v;
			break;
		case "bool":
			if(is_null($v) || (is_string($v) && !strlen($v))) return false;
			if(is_string($v) && strlen($v)) return true;
			if(is_numeric($v) && (int)$v === 1) return true;
			return false;
			break;
	}
	return $v;
}

/** 商品IDから商品オブジェクト **/
function soyshop_get_item_object(int $itemId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	if((int)$itemId <= 0) return new SOYShop_Item();

	$idx = soyshop_get_hash_index((string)$itemId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_item_hash_table"][$idx])) return $GLOBALS["soyshop_item_hash_table"][$idx];

	try{
        $GLOBALS["soyshop_item_hash_table"][$idx] = $dao->getById($itemId);
    }catch(Exception $e){
        $GLOBALS["soyshop_item_hash_table"][$idx] = new SOYShop_Item();
    }
	return $GLOBALS["soyshop_item_hash_table"][$idx];
}

/** 商品IDとカスタムフィールドのIDから商品属性のオブジェクトを取得する **/
function soyshop_get_item_attribute_object(int $itemId, string $fieldId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	if((int)$itemId <= 0 || !strlen($fieldId)) return new SOYShop_ItemAttribute();

	$idx = soyshop_get_hash_index(((string)$itemId . $fieldId), __FUNCTION__);
	if(isset($GLOBALS["soyshop_item_attribute_hash_table"][$idx])) return $GLOBALS["soyshop_item_attribute_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_item_attribute_hash_table"][$idx] = $dao->get($itemId, $fieldId);
	}catch(Exception $e){
		$attr = new SOYShop_ItemAttribute();
		$attr->setItemId($itemId);
		$attr->setFieldId($fieldId);
		$GLOBALS["soyshop_item_attribute_hash_table"][$idx] = $attr;
	}

	return $GLOBALS["soyshop_item_attribute_hash_table"][$idx];
}

function soyshop_get_item_attribute_value(int $itemId, string $fieldId, string $dataType=""){
	return soyshop_get_attribute_value(soyshop_get_item_attribute_object($itemId, $fieldId)->getValue(), $dataType);
}

function soyshop_save_item_attribute_object(SOYShop_ItemAttribute $attr){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

	if(is_string($attr->getValue())) $attr->setValue(trim($attr->getValue()));
	if(is_string($attr->getValue()) && !strlen($attr->getValue())) $attr->setValue(null);

	if(!is_null($attr->getValue()) || !is_null($attr->getExtraValues())){
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}else{
		try{
			$dao->delete($attr->getItemId(), $attr->getFieldId());
		}catch(Exception $e){
			//
		}
	}
}

/** 商品IDから子商品のリストを取得 **/
function soyshop_get_item_children(int $itemId, bool $isOpen=false, string $sortCondition="item_code desc"){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	if(!is_numeric($itemId)) return array();

	$idx = soyshop_get_hash_index((string)$itemId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_item_children_hash_table"][$idx])) return $GLOBALS["soyshop_item_children_hash_table"][$idx];

	$dao->setOrder($sortCondition);

	try{
		$GLOBALS["soyshop_item_children_hash_table"][$idx] = ($isOpen) ? $dao->getByTypeIsOpenNoDisabled($itemId) : $dao->getByTypeNoDisabled($itemId);
	}catch(Exception $e){
		$GLOBALS["soyshop_item_children_hash_table"][$idx] = array();
	}
	return $GLOBALS["soyshop_item_children_hash_table"][$idx];
}

/** カテゴリIDからカテゴリオブジェクトを取得する **/
function soyshop_get_category_object(int $categoryId){
    static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
    if($categoryId <= 0) return new SOYShop_Category();

	$idx = soyshop_get_hash_index((string)$categoryId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_category_hash_table"][$idx])) return $GLOBALS["soyshop_category_hash_table"][$idx];

    try{
        $GLOBALS["soyshop_category_hash_table"][$idx] = $dao->getById($categoryId);
    }catch(Exception $e){
        $GLOBALS["soyshop_category_hash_table"][$idx] = new SOYShop_Category();
    }

    return $GLOBALS["soyshop_category_hash_table"][$idx];
}

function soyshop_get_category_attribute_object(int $categoryId, string $fieldId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
	if((int)$categoryId <= 0 || !strlen($fieldId)) return new SOYShop_CategoryAttribute();

	$idx = soyshop_get_hash_index(((string)$categoryId . $fieldId), __FUNCTION__);
	if(isset($GLOBALS["soyshop_category_attribute_hash_table"][$idx])) return $GLOBALS["soyshop_category_attribute_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_category_attribute_hash_table"][$idx] = $dao->get($categoryId, $fieldId);
	}catch(Exception $e){
		$attr = new SOYShop_CategoryAttribute();
		$attr->setCategoryId($categoryId);
		$attr->setFieldId($fieldId);
		$GLOBALS["soyshop_category_attribute_hash_table"][$idx] = $attr;
	}

	return $GLOBALS["soyshop_category_attribute_hash_table"][$idx];
}

function soyshop_get_category_attribute_value(int $categoryId, string $fieldId, string $dataType=""){
	return soyshop_get_attribute_value(soyshop_get_category_attribute_object($categoryId, $fieldId)->getValue(), $dataType);
}

function soyshop_save_category_attribute_object(SOYShop_CategoryAttribute $attr){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");

	if(is_string($attr->getValue())) {
		$v = trim($attr->getValue());
		$v = (strlen($v)) ? $v : null;
		$attr->setValue($v);
	}
	if(!is_null($attr->getValue()) || !is_null($attr->getValue2())){
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}else{
		try{
			$dao->delete($attr->getCategoryId(), $attr->getFieldId());
		}catch(Exception $e){
			//
		}
	}
}

/** 顧客IDから顧客オブジェクトを取得する **/
function soyshop_get_user_object(int $userId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	if($userId <= 0) return new SOYShop_User();

	$idx = soyshop_get_hash_index((string)$userId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_user_hash_table"][$idx])) return $GLOBALS["soyshop_user_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_user_hash_table"][$idx] = $dao->getById($userId);
	}catch(Exception $e){
		$user = new SOYShop_User();
		$user->setName("[deleted]");
		$GLOBALS["soyshop_user_hash_table"][$idx] = $user;
	}
	return $GLOBALS["soyshop_user_hash_table"][$idx];
}

/** 顧客IDとカスタムフィールドのIDから顧客属性のオブジェクトを取得する **/
function soyshop_get_user_attribute_object(int $userId, string $fieldId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
	if((int)$userId <= 0 || !strlen($fieldId)) return new SOYShop_UserAttribute();

	$idx = soyshop_get_hash_index(((string)$userId . $fieldId), __FUNCTION__);
	if(isset($GLOBALS["soyshop_user_attribute_hash_table"][$idx])) return $GLOBALS["soyshop_user_attribute_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_user_attribute_hash_table"][$idx] = $dao->get($userId, $fieldId);
	}catch(Exception $e){
		$attr = new SOYShop_UserAttribute();
		$attr->setUserId($userId);
		$attr->setFieldId($fieldId);
		$GLOBALS["soyshop_user_attribute_hash_table"][$idx] = $attr;
	}

	return $GLOBALS["soyshop_user_attribute_hash_table"][$idx];
}

function soyshop_get_user_attribute_value(int $userId, string $fieldId, string $dataType=""){
	return soyshop_get_attribute_value(soyshop_get_user_attribute_object($userId, $fieldId)->getValue(), $dataType);
}

function soyshop_save_user_attribute_object(SOYShop_UserAttribute $attr){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_UserAttributeDAO");

	if(is_string($attr->getValue()) && !strlen($attr->getValue())) $attr->setValue(null);
	if(is_string($attr->getValue())) $attr->setValue(trim($attr->getValue()));
	if(!is_null($attr->getValue())){
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}else{
		try{
			$dao->delete($attr->getUserId(), $attr->getFieldId());
		}catch(Exception $e){
			//
		}
	}
}

/** ページIDからページオブジェクトを取得する **/
function soyshop_get_page_object(int $pageId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
	if($pageId <= 0) return new SOYShop_Page();

	$idx = soyshop_get_hash_index((string)$pageId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_page_hash_table"][$idx])) return $GLOBALS["soyshop_page_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_page_hash_table"][$idx] = $dao->getById($pageId);
	}catch(Exception $e){
		$GLOBALS["soyshop_page_hash_table"][$idx] = new SOYShop_Page();
	}

	return $GLOBALS["soyshop_page_hash_table"][$idx];
}

/** 注文IDから注文オブジェクトを取得する **/
function soyshop_get_order_object(int $orderId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	if($orderId <= 0) return new SOYShop_Order();

	$idx = soyshop_get_hash_index((string)$orderId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_order_hash_table"][$idx])) return $GLOBALS["soyshop_order_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_order_hash_table"][$idx] = $dao->getById($orderId);
	}catch(Exception $e){
		$GLOBALS["soyshop_order_hash_table"][$idx] = new SOYShop_Order();
	}

	return $GLOBALS["soyshop_order_hash_table"][$idx];
}

/** 注文IDとカスタムフィールドのIDから注文属性のオブジェクトを取得する **/
function soyshop_get_order_attribute_object(int $orderId, string $fieldId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
	if((int)$orderId <= 0 || !strlen($fieldId)) return new SOYShop_OrderAttribute();

	$idx = soyshop_get_hash_index(((string)$orderId . $fieldId), __FUNCTION__);
	if(isset($GLOBALS["soyshop_order_attribute_hash_table"][$idx])) return $GLOBALS["soyshop_order_attribute_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_order_attribute_hash_table"][$idx] = $dao->get($orderId, $fieldId);
	}catch(Exception $e){
		$attr = new SOYShop_OrderAttribute();
		$attr->setOrderId($orderId);
		$attr->setFieldId($fieldId);
		$GLOBALS["soyshop_order_attribute_hash_table"][$idx] = $attr;
	}

	return $GLOBALS["soyshop_order_attribute_hash_table"][$idx];
}

function soyshop_get_order_attribute_value(int $orderId, string $fieldId, string $dataType=""){
	return soyshop_get_attribute_value(soyshop_get_order_attribute_object($orderId, $fieldId)->getValue(), $dataType);
}

function soyshop_save_order_attribute_object(SOYShop_OrderAttribute $attr){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");

	if(is_string($attr->getValue())) {
		$v = trim($attr->getValue());
		$v = (strlen($v)) ? $v : null;
		$attr->setValue($v);
	}
	if(!is_null($attr->getValue()) || !is_null($attr->getValue2())){
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}else{
		try{
			$dao->delete($attr->getOrderId(), $attr->getFieldId());
		}catch(Exception $e){
			//
		}
	}
}

/** 注文IDとカスタムフィールドのIDから注文属性のオブジェクトを取得する **/
function soyshop_get_order_date_attribute_object(int $orderId, string $fieldId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
	if((int)$orderId <= 0 || !strlen($fieldId)) return new SOYShop_OrderDateAttribute();

	$idx = soyshop_get_hash_index(((string)$orderId . $fieldId), __FUNCTION__);
	if(isset($GLOBALS["soyshop_order_date_attribute_hash_table"][$idx])) return $GLOBALS["soyshop_order_date_attribute_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_order_date_attribute_hash_table"][$idx] = $dao->get($orderId, $fieldId);
	}catch(Exception $e){
		$attr = new SOYShop_OrderDateAttribute();
		$attr->setOrderId($orderId);
		$attr->setFieldId($fieldId);
		$GLOBALS["soyshop_order_date_attribute_hash_table"][$idx] = $attr;
	}
	
	return $GLOBALS["soyshop_order_date_attribute_hash_table"][$idx];
}

function soyshop_get_order_date_attribute_objects(int $orderId, array $fieldIds){
	if(!count($fieldIds)) return array();
	$attrs = array();
	foreach($fieldIds as $fieldId){
		$attrs[$fieldId] = soyshop_get_order_date_attribute_object($orderId, $fieldId);
	}
	return $attrs;
}

function soyshop_get_order_date_attribute_value(int $orderId, string $fieldId, string $dataType="", int $which=1){
	switch($which){
		case 2:
			$v = soyshop_get_order_date_attribute_object($orderId, $fieldId)->getValue2();
			break;
		default:
			$v = soyshop_get_order_date_attribute_object($orderId, $fieldId)->getValue();
	}
	return soyshop_get_attribute_value($v, $dataType);
}

function soyshop_save_order_date_attribute_object(SOYShop_OrderDateAttribute $attr){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");

	if(is_string($attr->getValue())) {
		$v = trim($attr->getValue());
		$v = (strlen($v)) ? $v : null;
		$attr->setValue($v);
	}
	if(!is_null($attr->getValue()) || !is_null($attr->getValue2())){
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}else{
		try{
			$dao->delete($attr->getOrderId(), $attr->getFieldId());
		}catch(Exception $e){
			//
		}
	}
}

/** IDもしくはプラグインIDからプラグインオブジェクトを取得する **/
function soyshop_get_plugin_object($pluginId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

	$idx = soyshop_get_hash_index((string)$pluginId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_plugin_hash_table"][$idx])) return $GLOBALS["soyshop_plugin_hash_table"][$idx];

	//最初に数字の場合を試して、次に文字列の場合を再度試して、ダメだったら空のオブジェクトを返す
	try{
		$GLOBALS["soyshop_plugin_hash_table"][$idx] = (is_numeric($pluginId)) ? $dao->getById($pluginId) : $dao->getByPluginId($pluginId);
	}catch(Exception $e){
		try{
			$GLOBALS["soyshop_plugin_hash_table"][$idx] = $dao->getByPluginId($pluginId);
		}catch(Exception $e){
			$GLOBALS["soyshop_plugin_hash_table"][$idx] = new SOYShop_PluginConfig();
		}
	}

	return $GLOBALS["soyshop_plugin_hash_table"][$idx];
}
