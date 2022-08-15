<?php

class AddMailTypeUtil {

	const MAIL_TYPE_ORDER = "order";
	const MAIL_TYPE_USER = "user";

	public static function getConfig(string $mode=self::MAIL_TYPE_ORDER){
		return SOYShop_DataSets::get(self::_getKey($mode), array());
	}

	public static function saveConfig(array $values, string $mode=self::MAIL_TYPE_ORDER){
		SOYShop_DataSets::put(self::_getKey($mode), $values);
	}

	private static function _getKey(string $mode){
		switch($mode){
			case self::MAIL_TYPE_USER:
				return "add_user_mail_type.config";
			default:
				return "add_mail_type.config";
		}
	}
}
