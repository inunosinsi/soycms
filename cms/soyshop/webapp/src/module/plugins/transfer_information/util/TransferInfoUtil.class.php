<?php

class TransferInfoUtil {

	const BANK_INFO = "transfer_bank_information";
	const PROP_BANK = "bank";		//金融機関名
	const PROP_BRANCH = "branch";	//支店名
	const PROP_DEPOSIT = "deposit";	//預金種別
	const PROP_NUMBER = "number";	//口座番号
	const PROP_HOLDER = "holder";	//口座名義

	private static function _depositTypes(){
		return array(
			"ordinary" => "普通預金",
			"current" => "当座預金"
		);
	}

	public static function getDepositTypeList(){
		return self::_depositTypes();
	}

	public static function getDepositType($t){
		$list = self::_depositTypes();
		return (isset($list[$t])) ? $list[$t] : $list["ordinary"];
	}

	public static function getUserAttr($userId, $fieldId){
		return self::_getUserAttr($userId, $fieldId);
	}

	private static function _getUserAttr($userId, $fieldId){
		try{
			return self::_userAttrDao()->get($userId, $fieldId);
		}catch(Exception $e){
			$obj = new SOYShop_UserAttribute();
			$obj->setUserId($userId);
			$obj->setFieldId($fieldId);
			return $obj;
		}
	}


	public static function saveAttr(SOYShop_UserAttribute $attr){
		if(strlen($attr->getValue())){
			try{
				self::_userAttrDao()->insert($attr);
			}catch(Exception $e){
				try{
					self::_userAttrDao()->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				self::_userAttrDao()->delete($attr->getUserId(), $attr->getFieldId());
			}catch(Exception $e){
				//
			}
		}
	}

	private static function _userAttrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		return $dao;
	}
}
