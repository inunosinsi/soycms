<?php
/*
 */
class ItemListCategoryCustomfieldInfo extends SOYShopInfoPageBase{

    function getPage(bool $active=false){

        if($active){
            return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_list_category_customfield") . '">カテゴリカスタムフィールド商品一覧モジュールの設定方法</a>';
        }else{
            return "";
        }
    }
}
SOYShopPlugin::extension("soyshop.info", "item_list_category_customfield", "ItemListCategoryCustomfieldInfo");
