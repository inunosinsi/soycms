<?php

class StoreUserFolderUtil{
	
	public static function getDownloadUrl(){
		return SOYSHOP_SITE_URL . soyshop_get_mypage_uri() . "?soyshop_download=store_user_folder&token=";
	}
}
?>