<?php

class LimitationBrowseUtil {

	const FIELD_ID = "browse_password";
	const COOKIE_KEY = "limitation_browse_allow_";

	/**
	 * パスワード認証に通過しているか？を調べる 通過している場合はtrue
	 * @param int entryId
	 * @return bool
	 */
	public static function checkIsAllowBrowse(int $entryId){
		// パスワードの登録が無い時は常にtrue
		if(!strlen(soycms_get_entry_attribute_value($entryId, self::FIELD_ID, "string"))) return true;

		// パスワード認証の通過に関するcookieが無い時 OR セッションの値が 1 でない時は常にfalse
		if(!isset($_COOKIE[self::COOKIE_KEY . $entryId]) || (int)$_COOKIE[self::COOKIE_KEY . $entryId] !== 1) return false;

		// cookieの更新
		self::save($entryId);

		return true;
	}

	public static function save(int $entryId, bool $isAuto=false){
		if(!$isAuto) $isAuto = (isset($_COOKIE[self::COOKIE_KEY . "auto_auth_" . $entryId]) && (int)$_COOKIE[self::COOKIE_KEY . "auto_auth_" . $entryId] === 1);
		
		if($isAuto){
			$n = 30;
			$h = 24;
		}else{
			$n = 1;
			$h = 1;
		}
		if($n > 1) soy2_setcookie(self::COOKIE_KEY . "auto_auth_" . $entryId, 1, array("path" => "/", "expires" => (time() + ($n * $h * 60 * 60))));
		soy2_setcookie(self::COOKIE_KEY . $entryId, 1, array("path" => "/", "expires" => (time() + ($n * $h * 60 * 60))));
	}
}