<?php
class SOYShopItemListSale extends SOYShopItemListBase{

    /**
     * @return string
     */
    function getLabel(){
        return "ItemListSale";
    }

    /**
     * @return array
     */
    function getItems($pageObj, $offset, $limit){
        //SQL直接読み込みでいくか…

        SOY2::import("util.SOYShopPluginUtil");
        //セール期間の設定を行っている場合
        if((SOYShopPluginUtil::checkIsActive("common_sale_period"))){
            $res = SOY2Logic::createInstance("module.plugins.common_sale_period.logic.SearchLogic")->searchItems($offset, $limit);
        //セール期間の設定を行っていない場合
        }else{
            $logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
                "sort" => $pageObj
            ));
            $params = array();
            $params["item_sale_flag"] = 1;    //セールスフラグを立てる
            $res = $logic->searchItems(array(), array(), $params, $offset, $limit);
        }
        return (isset($res[0])) ? $res[0] : array();
    }

    /**
     * @return number
     */
    function getTotal($pageObj){
        SOY2::import("util.SOYShopPluginUtil");
        //セール期間の設定を行っている場合
        if((SOYShopPluginUtil::checkIsActive("common_sale_period"))){
            return SOY2Logic::createInstance("module.plugins.common_sale_period.logic.SearchLogic")->countItems();
        //セール期間の設定を行っていない場合
        }else{
            SOY2::import("domain.shop.SOYShop_Item");

            $query = new SOY2DAO_Query();
            $query->prefix = "select";
            $query->sql = "count(id) as item_count";
            $query->where = "item_sale_flag = " . SOYShop_Item::IS_SALE . " AND item_is_open = " . SOYShop_Item::IS_OPEN . " AND is_disabled != " . SOYShop_Item::IS_DISABLED . " AND open_period_start < " . time() . " AND open_period_end > " . time();
            $query->table = "soyshop_item";

            $binds = array();

            $itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
            try{
                $res = $itemDAO->executeOpenItemQuery($query, $binds);
            }catch(Exception $e){
                return 0;
            }


            return (isset($res[0]["item_count"])) ? (int)$res[0]["item_count"] : 0;
        }
    }
}

SOYShopPlugin::extension("soyshop.item.list", "item_list_sale", "SOYShopItemListSale");
