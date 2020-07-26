<?php

class CustomIconFieldUtil {

	/**
	 * アイコンディレクトリを取得
	 */
	public static function getIconDirectory(){
		return UserInfoUtil::getSiteDirectory() . "icons";
	}
}
