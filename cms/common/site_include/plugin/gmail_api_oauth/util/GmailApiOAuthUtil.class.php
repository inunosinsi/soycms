<?php

class GmailApiOAuthUtil {

	CONST CALLBACK_URI = "/gmail/smtp/oauth";
	CONST SECRET = "client_secret.json";
	CONST CONFIG_FILE_NAME = "gmail_oauth.json";

	public static function getCallbackUrl(){
		SOY2::import("site_include.func.public", ".php");
		return soycms_get_site_url_by_frontcontroller(true) . ltrim(self::CALLBACK_URI,"/");
	}

	/**
	 * @return string
	 */
	public static function getTokenDir(){
		return SOY2::rootDir()."config/api/";
	}

	/**
	 * @return string
	 */
	public static function getClientSecretJsonPath(){
		return self::getTokenDir().self::SECRET;
	}

	public static function getGmailConfigFilePath(){
		return SOY2::rootDir()."config/api/".self::CONFIG_FILE_NAME;
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
