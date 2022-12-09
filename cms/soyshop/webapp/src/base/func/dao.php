<?php

/** 共通且つ効率化する関数群 **/
//ハッシュテーブル用のハッシュ値を作成する
function soyshop_generate_hash_value(string $str, int $length=12){
	$hash = md5($str . (string)SOY2DAOConfig::Dsn());	//dnsは他サイトブロックを使用した時の対策
	return substr($hash, 0, $length);
}

function soyshop_get_hash_table_types(){
	static $types;
	if(is_null($types)) {
		$types = array(
			"item", 
			"item_children", 
			"item_attribute", 
			"category", 
			"category_attribute", 
			"user", 
			"user_attribute", 
			"order", 
			"order_attribute", 
			"order_date_attribute", 
			"order_state_history", 
			"item_orders", 
			"page", 
			"plugin", 
			"data_sets",
			"schedule_calendar", 
			"schedule_search",
			"schedule_price",
			"reserve_calendar",
			"reserve_cancel"
		);
	}
	return $types;
}

function soyshop_get_hash_table_mode(string $fnName){
	if(is_bool(strpos($fnName, "soyshop_"))) return $fnName;
	$fnName = str_replace(array("soyshop_get_", "soyshop_save_", "_objects", "_object"), "", $fnName);
	if(is_numeric(strpos($fnName, "_by_"))) $fnName = substr($fnName, 0, strpos($fnName, "_by_"));
	return $fnName;
}

//ハッシュ値を記録したテーブルを用いてインデックスを検索する
function soyshop_get_hash_index(string $str, string $fnName){
	static $tables;
	if(is_null($tables)) $tables = array_fill(0, count(soyshop_get_hash_table_types()), array());

	$idx = array_search(soyshop_get_hash_table_mode($fnName), soyshop_get_hash_table_types());
	$hash = soyshop_generate_hash_value($str);
	if(!count($tables[$idx]) || !is_numeric(array_search($hash, $tables[$idx]))) $tables[$idx][] = $hash;
	return array_search($hash, $tables[$idx]);
}

function soyshop_get_hash_table_dao(string $fnName){
	static $daos;
	if(is_null($daos)) $daos = array_fill(0, count(soyshop_get_hash_table_types()), null);

	$idx = array_search(soyshop_get_hash_table_mode($fnName), soyshop_get_hash_table_types());
	if(!is_null($daos[$idx])) return $daos[$idx];

	switch($idx){
		case 0:	//item
		case 1:	//item_child
			$path = "shop.SOYShop_ItemDAO";
			break;
		case 2:	//item_attribute
			$path = "shop.SOYShop_ItemAttributeDAO";
			break;
		case 3:	//category
			$path = "shop.SOYShop_CategoryDAO";
			break;
		case 4:	//category_attribute
			$path = "shop.SOYShop_CategoryAttributeDAO";
			break;
		case 5:	//user
			$path = "user.SOYShop_UserDAO";
			break;
		case 6:
			$path = "user.SOYShop_UserAttributeDAO";
			break;
		case 7:
			$path = "order.SOYShop_OrderDAO";
			break;
		case 8:
			$path = "order.SOYShop_OrderAttributeDAO";
			break;
		case 9:
			$path = "order.SOYShop_OrderDateAttributeDAO";
			break;
		case 10:
			$path = "order.SOYShop_OrderStateHistoryDAO";
			break;
		case 11:	// item_orders
			$path = "order.SOYShop_ItemOrderDAO";
			break;
		case 12:
			$path = "site.SOYShop_PageDAO";
			break;
		case 13:
			$path = "plugin.SOYShop_PluginConfigDAO";
			break;
		case 14:
			$path = "config.SOYShop_DataSetsDAO";
			break;
		case 15:	// schedule_calendar
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
			$path = "SOYShopReserveCalendar_ScheduleDAO";
			break;
		case 16:	// schedule_search
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleSearchDAO");
			$path = "SOYShopReserveCalendar_ScheduleSearchDAO";
			break;
		case 17:	// schedule_price
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_SchedulePriceDAO");
			$path = "SOYShopReserveCalendar_SchedulePriceDAO";
			break;
		case 18:	// reserve_calendar
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
			$path = "SOYShopReserveCalendar_ReserveDAO";
			break;
		case 19:	// reserve_cancel
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_CancelDAO");
			$path = "SOYShopReserveCalendar_CancelDAO";
			break;

	}
	$daos[$idx] = SOY2DAOFactory::create($path);
	return $daos[$idx];
}

/**
 * 配列から指定の値を除き、間を詰める
 */
function soyshop_remove_value_on_array(string $fieldId, array $fieldIds){
	if(!count($fieldIds)) return array($fieldIds);
	$idx = array_search($fieldId, $fieldIds);
	if(!is_numeric($idx)) return $fieldIds;

	unset($fieldIds[$idx]);
	return array_values($fieldIds);
}

/** 各種オブジェクトを取得する関数群 **/
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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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

function soyshop_get_item_object_by_code(string $code){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
	if(!strlen($code)) return new SOYShop_Item();

	try{
		$item = $dao->getByCode($code);
	}catch(Exception $e){
		return new SOYShop_Item();
	}

	$idx = soyshop_get_hash_index((string)$item->getId(), __FUNCTION__);
	if(!isset($GLOBALS["soyshop_item_hash_table"][$idx])) $GLOBALS["soyshop_item_hash_table"][$idx] = $item;
	return $item;
}

/** 商品IDとカスタムフィールドのIDから商品属性のオブジェクトを取得する **/
function soyshop_get_item_attribute_object(int $itemId, string $fieldId){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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

function soyshop_get_item_attribute_objects(int $itemId, array $fieldIds){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
	if(!count($fieldIds)) return array();

	$attrs = array();
	foreach($fieldIds as $fieldId){
		$idx = soyshop_get_hash_index(((string)$itemId . $fieldId), __FUNCTION__);
		if(!isset($GLOBALS["soyshop_item_attribute_hash_table"][$idx])) continue;
		$attrs[$fieldId] = $GLOBALS["soyshop_item_attribute_hash_table"][$idx];
	}
	if(count($attrs)){
		foreach($attrs as $fieldId => $_dust){
			$fieldIds = soyshop_remove_value_on_array($fieldId, $fieldIds);
		}
		unset($_dust);
	}
	if(!count($fieldIds)) return $attrs;

	$attrs = $dao->getByItemIdAndFieldIds($itemId, $fieldIds);
	foreach($attrs as $fieldId => $attr){
		$idx = soyshop_get_hash_index(((string)$itemId . $fieldId), __FUNCTION__);
		if(!isset($GLOBALS["soyshop_item_attribute_hash_table"][$idx])) $GLOBALS["soyshop_item_attribute_hash_table"][$idx] = $attr;
	}
	return $attrs;
}

function soyshop_get_item_attribute_value(int $itemId, string $fieldId, string $dataType=""){
	return soyshop_get_attribute_value(soyshop_get_item_attribute_object($itemId, $fieldId)->getValue(), $dataType);
}

function soyshop_save_item_attribute_object(SOYShop_ItemAttribute $attr){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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
    $dao = soyshop_get_hash_table_dao(__FUNCTION__);
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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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

function soyshop_get_user_object_by_mailaddress(string $mailAddress){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
	if(strlen($mailAddress) <= 0) return new SOYShop_User();

	try{
		$user = $dao->getByMailAddress($mailAddress);
	}catch(Exception $e){
		$user = new SOYShop_User();
		$user->setMailAddress($mailAddress);
		return $user;
	}

	$idx = soyshop_get_hash_index((string)$user->getId(), __FUNCTION__);
	if(!isset($GLOBALS["soyshop_user_hash_table"][$idx])) $GLOBALS["soyshop_user_hash_table"][$idx] = $user;
	return $user;
}

/** 顧客IDとカスタムフィールドのIDから顧客属性のオブジェクトを取得する **/
function soyshop_get_user_attribute_object(int $userId, string $fieldId){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

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

/** 注文IDから注文オブジェクトを取得する **/
function soyshop_get_order_object(int $orderId){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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
function soyshop_get_order_attribute_object(int $orderId, string $fieldId, bool $isForce=false){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
	if((int)$orderId <= 0 || !strlen($fieldId)) return new SOYShop_OrderAttribute();

	$idx = soyshop_get_hash_index(((string)$orderId . $fieldId), __FUNCTION__);
	if(!$isForce && isset($GLOBALS["soyshop_order_attribute_hash_table"][$idx])) return $GLOBALS["soyshop_order_attribute_hash_table"][$idx];

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

function soyshop_get_order_attribute_value(int $orderId, string $fieldId, string $dataType="", bool $isForce=false){
	return soyshop_get_attribute_value(soyshop_get_order_attribute_object($orderId, $fieldId, $isForce)->getValue(), $dataType);
}

function soyshop_save_order_attribute_object(SOYShop_OrderAttribute $attr){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
	if(!count($fieldIds)) return array();

	$attrs = array();
	foreach($fieldIds as $fieldId){
		$idx = soyshop_get_hash_index(((string)$orderId . $fieldId), __FUNCTION__);
		if(!isset($GLOBALS["soyshop_order_date_attribute_hash_table"][$idx])) continue;
		$attrs[$fieldId] = $GLOBALS["soyshop_order_date_attribute_hash_table"][$idx];
	}
	if(count($attrs)){
		foreach($attrs as $fieldId => $_dust){
			$fieldIds = soyshop_remove_value_on_array($fieldId, $fieldIds);
		}
		unset($_dust);
	}

	if(!count($fieldIds)) return $attrs;

	$arr = $dao->getByOrderIdAndFieldIds($orderId, $fieldIds);
	foreach($arr as $fieldId => $attr){
		if(isset($attrs[$fieldId])) continue;
		$attrs[$fieldId] = $attr;
		$idx = soyshop_get_hash_index(((string)$orderId . $fieldId), __FUNCTION__);
		if(!isset($GLOBALS["soyshop_order_date_attribute_hash_table"][$idx])) $GLOBALS["soyshop_order_date_attribute_hash_table"][$idx] = $attr;
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

function soyshop_get_order_date_attribute_values(int $orderId, array $fieldIds, string $dataType="", int $which=1){
	if(!count($fieldIds)) return array();
	$attrs = soyshop_get_order_date_attribute_objects($orderId, $fieldIds);

	$list = array();
	foreach($attrs as $fieldId => $attr){
		switch($which){
			case 2:
				$list[$fieldId] = soyshop_get_attribute_value($attr->getValue2());
				break;
			default:
				$list[$fieldId] = soyshop_get_attribute_value($attr->getValue1());
		}
	}
	
	return $list;
}

function soyshop_save_order_date_attribute_object(SOYShop_OrderDateAttribute $attr){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

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

/** 何度も出てくる処理なので、わかりやすいように **/
function soyshop_get_item_orders(int $orderId){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

	$idx = soyshop_get_hash_index((string)$orderId, __FUNCTION__);
	if(isset($GLOBALS["soyshop_item_orders_hash_table"][$idx])) return $GLOBALS["soyshop_item_orders_hash_table"][$idx];

	try{
		$GLOBALS["soyshop_item_orders_hash_table"][$idx] = $dao->getByOrderId($orderId);
	}catch(Exception $e){
		$GLOBALS["soyshop_item_orders_hash_table"][$idx] = array();
	}
	return $GLOBALS["soyshop_item_orders_hash_table"][$idx];
}

/** ページIDからページオブジェクトを取得する **/
function soyshop_get_page_object(int $pageId){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
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

function soyshop_get_page_object_by_uri(string $uri){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);
	if(!strlen($uri)) return new SOYShop_Page();
	try{
		$page = $dao->getByUri($uri);
	}catch(Exception $e){
		$page = new SOYShop_Page();
		if(is_bool(strpos($e->getMessage(), "Failed to return Object"))) $page->setId(-1);	// 任意のuriでページが取得できなかった場合はID:-1を指定
		return $page;
	}

	$idx = soyshop_get_hash_index((string)$page->getId(), __FUNCTION__);
	if(!isset($GLOBALS["soyshop_page_hash_table"][$idx])) $GLOBALS["soyshop_page_hash_table"][$idx] = $page;
	return $page;
}

/** IDもしくはプラグインIDからプラグインオブジェクトを取得する **/
function soyshop_get_plugin_object($pluginId){
	$dao = soyshop_get_hash_table_dao(__FUNCTION__);

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

/** ダミーを取得する関数群 **/
//ダミーの商品コードを取得する
function soyshop_dummy_item_code(){
	if(defined("_SITE_ROOT_") || defined("CMS_LABEL_ICON_DIRECTORY")) return "";	// SOY CMS側から呼び出した時はこの関数は使用しない
	
	SOY2::import("domain.config.SOYShop_ShopConfig");
	$config = SOYShop_ShopConfig::load();
	if((int)$config->getInsertDummyItemCode() !== 1) return "";

	$rule = $config->getDummyItemCodeRule();

	$dao = soyshop_get_hash_table_dao("item");

	for(;;){
		if(is_null($rule) || !strlen($rule)){
			$code = soyshop_create_random_string(8);
		}else{
			try{
				$res = $itemDao->executeQuery("SELECT item_code FROM soyshop_item WHERE item_code LIKE :code ORDER BY item_code DESC LIMIT 1", array(":code" => $rule . "%"));
			}catch(Exception $e){
				$res = array();
			}

			if(isset($res[0]["item_code"])){
				preg_match('/^' . $rule . '(.*)/', $res[0]["item_code"], $tmp);
				$n = (int)$tmp[1] + 1;
			}else{
				$n = 1;
			}

			if(strlen($n) < 3) $n = "00" . $n;
			$code = $rule . $n;
		}

		$item = soyshop_get_item_object_by_code($code);
		if(!is_numeric($item->getId())) break;
	}

	return $code;
}

//ダミーのメールアドレスを取得する
function soyshop_dummy_mail_address(){
    //ランダムなメールアドレスを取得する。一応重複チェックも行う
	$mailAddress = "";
    for(;;){
        $mailAddress = soyshop_create_random_string(10) . "@" . DUMMY_MAIL_ADDRESS_DOMAIN;
		if(!is_numeric(soyshop_get_user_object_by_mailaddress($mailAddress)->getId())) break;
    }
    return $mailAddress;
}
