<?php

class SOYShopCacheUtil {

    public static function clearCache(){
        if(!file_exists("soyshop_clear_cache")) include_once(SOY2::RootDir() . "base/func/admin.php");
        soyshop_clear_cache();
    }
}