<?php

class CancelMailUtil {

    const MODE_EMAIL = "email";
    const PLUGIN_ID = "cancel_mail";

    public static function get($itemId, $mode = self::MODE_FEE){
        static $dao;
        if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
        try{
            return $dao->get($itemId, self::PLUGIN_ID . "_" . $mode)->getValue();
        }catch(Exception $e){
            return null;
        }
	}
}
