<?php
if(!defined("CMS_COMMON")) define("CMS_COMMON", dirname(dirname(dirname(SOY2::rootDir())))."/common/");

class GmailApiOAuthUtil {

	CONST CALLBACK_URI = "/gmail/smtp/oauth";
	CONST SECRET = "client_secret.json";
	CONST CONFIG_FILE_NAME = "gmail_oauth.json";

	public static function getCallbackUrl(){
		return SOYSHOP_SITE_URL . ltrim(self::CALLBACK_URI,"/");
	}

	/**
	 * @return string
	 */
	public static function getTokenDir(){
		return CMS_COMMON."config/api/";
	}

	/**
	 * @return string
	 */
	public static function getClientSecretJsonPath(){
		return self::getTokenDir().self::SECRET;
	}

	public static function getGmailConfigFilePath(){
		return CMS_COMMON."config/api/".self::CONFIG_FILE_NAME;
	}

	/**
	 * @return bool
	 */
	public static function checkPermission(){
		return is_writable(self::getTokenDir());
	}

	/**
	 * @return bool
	 */
	public static function checkClientSecret(){
		return file_exists(self::getClientSecretJsonPath());
	}

	/**
	 * @return bool
	 */
	public static function checkGmailToken(){
		return file_exists(self::getGmailConfigFilePath());
	}
}
