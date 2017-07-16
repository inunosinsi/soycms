<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CustomSearchFieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

    function beforeOutput($page){

        //カートとマイページで動作しない様にする
        if(is_null($page->getPageObject())) return;
        if($page->getPageObject()->getType() == SOYShop_Page::TYPE_COMPLEX || $page->getPageObject()->getType() == SOYShop_Page::TYPE_FREE)

        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");

        //一覧で動作する
        if($page->getPageObject()->getType() == SOYShop_Page::TYPE_LIST || $page->getPageObject()->getType() == SOYShop_Page::TYPE_SEARCH){

            //_homeでもソートボタン設置プラグインを使用できるようにする
            if($page->getPageObject()->getUri() == SOYShop_Page::URI_HOME){
                $pageUrl = soyshop_get_page_url(null);
            }else{
                $pageUrl = soyshop_get_page_url($page->getPageObject()->getUri());
            }

            //検索結果ページの内容をそのまま引き継ぐ
            $query = "";
            if(strlen($_SERVER["QUERY_STRING"]) && strpos($_SERVER["QUERY_STRING"], "&")){
                //値の整理をしながら
                $queries = explode("&", $_SERVER["QUERY_STRING"]);
                if(count($queries)){
                    foreach($queries as $q){
                        if(strpos($q, "=") === false) continue;

                        //custom_search_sortとrは除く
                        if(strpos($q, "custom_search_sort=") === 0 || strpos($q, "r=") === 0) continue;

                        $query .= "&" . $q;
                    }
                }
            }

            $args = $page->getArguments();
            for($i = 0; $i < count($args); $i++){
                if(isset($args[$i]) && strlen($args[$i])){
                    $pageUrl .= "/" . htmlspecialChars($args[$i], ENT_QUOTES, "UTF-8");
                }
            }

            foreach(CustomSearchFieldUtil::getConfig() as $fieldId => $values){
                $page->addLink("custom_search_sort_" . $fieldId . "_desc", array(
                    "soy2prefix" => "css",
                    "link" => $pageUrl . "?custom_search_sort=" . $fieldId . "&r=1" . $query
                ));

                $page->addLink("custom_search_sort_" . $fieldId . "_asc", array(
                    "soy2prefix" => "css",
                    "link" => $pageUrl . "?custom_search_sort=" . $fieldId . "&r=0" . $query
                ));
            }
        }

        //カテゴリカスタムサーチフィールド
        switch($page->getPageObject()->getType()){
          case SOYShop_Page::TYPE_LIST:
            $categoryId = $page->getPageObject()->getObject()->getCurrentCategory()->getId();
            break;
          case SOYShop_Page::TYPE_DETAIL:
            $item = $page->getPageObject()->getObject()->getCurrentItem();
            if(!is_null($item)){
              $categoryId = $item->getCategory();
            }else{
              $categoryId = null;
            }
            break;
          default:
            $categoryId = null;
        }
        if(is_null($categoryId)) return;

        $values = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic", array("mode" => "category"))->getByCategoryId($categoryId);
        foreach(CustomSearchFieldUtil::getCategoryConfig() as $key => $field){

            //多言語化対応はデータベースから値を取得した時点で行っている
            $csfValue = $values[$key];

            $page->addModel($key . "_visible", array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
                "visible" => (strlen($csfValue))
            ));

            $page->addLabel($key, array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
                "html" => (isset($csfValue)) ? $csfValue : null
            ));

            switch($field["type"]){
                case CustomSearchFieldUtil::TYPE_CHECKBOX:
                    if(strlen($field["option"][SOYSHOP_PUBLISH_LANGUAGE])){
                        $vals = explode(",", $csfValue);
                        $opts = explode("\n", $field["option"][SOYSHOP_PUBLISH_LANGUAGE]);
                        foreach($opts as $i => $opt){
                            $opt = trim($opt);
                            $page->addModel($key . "_"  . $i . "_visible", array(
                                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
                                "visible" => (in_array($opt, $vals))
                            ));

                            $page->addLabel($key . "_" . $i, array(
                                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
                                "text" => $opt
                            ));
                        }
                    }
                    break;
            }
        }
    }
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "custom_search_field", "CustomSearchFieldBeforeOutput");
