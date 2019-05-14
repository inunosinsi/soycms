<?php

class AddMailAddressEachItemUtil {

    const MODE_EMAIL = "email";
    const PLUGIN_ID = "add_mailaddress_each_item";

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
