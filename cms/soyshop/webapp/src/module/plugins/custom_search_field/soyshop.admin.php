<?php
class CustomSearchFieldAdmin extends SOYShopAdminBase{

    function execute(){
        //多言語化
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
            $langs = UtilMultiLanguageUtil::allowLanguages();
        }else{
            $langs = array(self::LANGUAGE_JP => "日本語");
        }

        $dao = new SOY2DAO();
        foreach($langs as $lang => $v){
            $langId = UtilMultiLanguageUtil::getLanguageId($lang);
            //現在登録されている最新の商品IDを取得する
            try{
                $res = $dao->executeQuery("SELECT item_id FROM soyshop_custom_search WHERE lang = :lang ORDER BY item_id DESC LIMIT 1;", array(":lang" => $langId));
            }catch(Exception $e){
                $res = array();
            }

            $lastItemId = (isset($res[0]["item_id"])) ? (int)$res[0]["item_id"] : 0;

            //最新の商品IDよりも上のIDがあるか調べる
            try{
                $res = $dao->executeQuery("SELECT id FROM soyshop_item WHERE id > :itemId", array(":itemId" => $lastItemId));
            }catch(Exception $e){
                return;
            }

            if(count($res)){
                foreach($res as $v){
                    $sql = "INSERT INTO soyshop_custom_search (item_id, lang) VALUES (" . $v["id"] . "," . $langId . ")";
                    try{
                        $dao->executeQuery($sql);
                    }catch(Exception $e){
                        //
                    }
                }
            }
        }
    }
}
SOYShopPlugin::extension("soyshop.admin", "custom_search_field", "CustomSearchFieldAdmin");
