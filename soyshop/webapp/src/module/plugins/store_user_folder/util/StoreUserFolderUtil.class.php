<?php

class StoreUserFolderUtil{

	public static function getDownloadUrl(){
		return soyshop_get_mypage_url(true) . "?soyshop_download=store_user_folder&token=";
	}
}
