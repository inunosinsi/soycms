<?php

class RefundManagerUtil {

	const FIELD_ID = "refund_manager";
	const TYPE_CANCEL = "cancel";
	const TYPE_CHANGE = "change";

	const ACCOUNT_TYPE_NORMAL = 1;
	const ACCOUNT_TYPE_CURRENT = 2;

	public static function save(array $params, bool $isProcessed, int $orderId){
		$attr = soyshop_get_order_attribute_object($orderId, self::FIELD_ID);
		$v = (isset($params["type"])) ? soy2_serialize($params) : null;
		$v2 = ($isProcessed) ? 1 : null;
		$attr->setValue1($v);
		$attr->setValue2($v2);
		soyshop_save_order_attribute_object($attr);
	}

	public static function get(int $orderId, bool $everytime=false){
		if($everytime){	//隠しモード
			return self::_get2($orderId);
		}else{
			return self::_get($orderId);
		}
	}

	public static function getTypeTextByOrderId(int $orderId, bool $everytime=false){
		if($everytime){		//隠しモード
			list($values, $isProcessed) = self::_get2($orderId);
		}else{
			list($values, $isProcessed) = self::_get($orderId);
		}

		if(!isset($values["type"])) return "";
		switch($values["type"]){
			case self::TYPE_CANCEL:
				return "キャンセル";
			case self::TYPE_CHANGE:
				return "変更";
		}
	}

	public static function getAccountTypeList(){
		return self::_getAccountTypeList();
	}

	public static function getAccountTypeText(int $type){
		$types = self::_getAccountTypeList();
		return (isset($types[$type])) ? $types[$type] : "普通";
	}

	private static function _getAccountTypeList(){
		return array(
			self::ACCOUNT_TYPE_NORMAL => "普通",
			self::ACCOUNT_TYPE_CURRENT => "当座"
		);
	}

	private static function _get(int $orderId){
		static $values, $isProcessed;
		if(is_null($values)){
			try{
				$attr = soyshop_get_order_attribute_object($orderId, self::FIELD_ID);
				//設置値と処理済みかどうかを返す
				$values = soy2_unserialize((string)$attr->getValue1());
				$isProcessed = (int)$attr->getValue2();
			}catch(Exception $e){
				$values = array();
				$isProcessed = 0;
			}
		}
		return array($values, $isProcessed);
	}

	//値のみ取得	隠し機能
	private static function _get2(int $orderId){
		try{
			$attr = soyshop_get_order_attribute_object($orderId, self::FIELD_ID);
			//設置値と処理済みかどうかを返す
			$values = soy2_unserialize((string)$attr->getValue1());
			$isProcessed = (int)$attr->getValue2();
		}catch(Exception $e){
			$values = array();
			$isProcessed = 0;
		}
		return array($values, $isProcessed);
	}
}
