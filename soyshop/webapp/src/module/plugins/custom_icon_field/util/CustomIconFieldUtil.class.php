<?php

class CustomIconFieldUtil {

	public static function getIconDirectory(){
		return SOYSHOP_SITE_DIRECTORY . "files/custom-icons/";
	}

	public static function getIconPath(){
		$siteUrl = str_replace(array("https://", "http://"), "//", SOYSHOP_SITE_URL);
		return $siteUrl . "files/custom-icons/";
	}
}
