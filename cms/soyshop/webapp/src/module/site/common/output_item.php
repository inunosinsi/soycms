<?php
SOYShopPlugin::load("soyshop.item.customfield");
SOY2::import("domain.config.SOYShop_ShopConfig");

/**
 * 商品情報を出力
 * テンプレートに記述しない
 */
function soyshop_output_item($htmlObj, SOYShop_Item $item, $obj=null){
    static $itemDao, $categoryDao, $shopConfig;
    if(is_null($itemDao)) $itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
    if(is_null($categoryDao)) $categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
	if(is_null($shopConfig)) $shopConfig = SOYShop_ShopConfig::load();

    //グループの場合の処理
    $childItems = array();
    if($item->getType() == SOYShop_Item::TYPE_GROUP || $item->getType() == SOYShop_Item::TYPE_DOWNLOAD_GROUP){
        $type = (method_exists($obj, "getSortType")) ? $obj->getSortType() : "item_code";
        $order = (method_exists($obj, "getSortOrder") && $obj->getSortOrder() == 1) ? $type . " desc" : $type . " asc";
        $logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
        ));
        $childItems = $logic->getChildItems($item->getId(), $order);
    }
    if(!$htmlObj instanceof SOYShop_ChildItemListComponent){
        $htmlObj->createAdd("child_item_list", "SOYShop_ChildItemListComponent", array(
            "list" => $childItems,
            "soy2prefix" => "block"
        ));
    }

    $htmlObj->addLabel("id", array(
        "text" => $item->getId(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //商品名
    $htmlObj->addLabel("item_name", array(
        "text" => $item->getOpenItemName(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));


    //表示価格が0円以上の場合は表示する
    $htmlObj->addModel("item_price_visible", array(
        "visible" => ((int)$item->getSellingPrice() > 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //表示価格(通常価格、セール設定中はセール価格)
    $htmlObj->addLabel("item_price", array(
        "text" => soyshop_display_price($item->getSellingPrice()),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addModel("item_normal_price_visible", array(
        "visible" => ((int)$item->getPrice() > 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //通常価格
    $htmlObj->addLabel("item_normal_price", array(
        "text" => soyshop_display_price($item->getPrice()),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addModel("item_sale_price_visible", array(
        "visible" => ((int)$item->getSalePrice() > 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //セール価格
    $htmlObj->addLabel("item_sale_price", array(
        "text" => soyshop_display_price($item->getSalePrice()),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));


    $htmlObj->addModel("item_list_price_visible", array(
        "visible" => ((int)$item->getAttribute("list_price") > 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //定価
    $htmlObj->addLabel("item_list_price", array(
        "text" => soyshop_display_price($item->getAttribute("list_price")),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //セール設定中のみ表示される
    $htmlObj->addModel("on_sale", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => $item->getSaleFlag()
    ));

    //セール設定中は表示されない
    $htmlObj->addModel("not_on_sale", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => (!$item->getSaleFlag())
    ));

    //通常価格とセール価格が異なる時のみ表示する
    $htmlObj->addModel("is_normal_price_diff_from_sale_price", array(
      "soy2prefix" => SOYSHOP_SITE_PREFIX,
      "visible" => ((int)$item->getPrice() !== (int)$item->getSalePrice())
    ));

    //定価から表示価格の割引率
    $htmlObj->addLabel("item_discount_percentage", array(
        "text" => ($item->getSellingPrice() > 0 && $item->getAttribute("list_price") > 0) ? soyshop_display_price(100 - ($item->getSellingPrice() / $item->getAttribute("list_price") * 100)) : 0,
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => ($item->getSellingPrice() > 0 && $item->getAttribute("list_price") > 0)
    ));

    //定価から通常価格の割引率
    $htmlObj->addLabel("item_normal_discount_percentage", array(
        "text" => ($item->getPrice() > 0 && $item->getAttribute("list_price") > 0) ? soyshop_display_price(100 - ($item->getPrice() / $item->getAttribute("list_price") * 100)) : 0,
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => ($item->getPrice() > 0 && $item->getAttribute("list_price") > 0)
    ));

    //定価からセール価格の割引率
    $htmlObj->addLabel("item_sale_discount_percentage", array(
        "text" => ($item->getSalePrice() > 0 && $item->getAttribute("list_price") > 0) ? soyshop_display_price(100 - ($item->getSalePrice() / $item->getAttribute("list_price") * 100)) : 0,
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => ($item->getSalePrice() > 0 && $item->getAttribute("list_price") > 0)
    ));

    //在庫数
    $htmlObj->addLabel("item_stock", array(
        "text" => $item->getStock(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

	$htmlObj->addModel("is_stock", array(
        "visible" => ($shopConfig->getIgnoreStock() == 1 || $item->getStock() > 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addModel("no_stock", array(
        "visible" => ($item->getStock() == 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addLabel("item_code", array(
        "text" => $item->getCode(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $categoryObj = soyshop_get_category_object($item->getCategory());

    //カテゴリの表示
    $htmlObj->addLabel("category_name", array(
        "text" => $categoryObj->getOpenCategoryName(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addLink("category_link", array(
        "link" => soyshop_get_item_list_link($item, $categoryObj),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    //cms:id="item_small_image"とcms:id="item_large_image"
    foreach(array("small", "large") as $tp){
        $img = soyshop_convert_file_path($item->getAttribute("image_" . $tp), $item);
        $key = "item_" . $tp . "_image";
        $htmlObj->addImage($key, array(
            "src" => $img,
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
            "visible" => (strlen($img) > 0)
        ));
        $htmlObj->addLink($key . "_link", array(
            "link" => $img,
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
            "visible" => (strlen($img) > 0)
        ));

        $htmlObj->addLabel($key . "_url", array(
            "text" => $img,
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
        ));

        $htmlObj->addModel($key . "_show", array(
            "visible" => (strlen($img) > 0),
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
        ));
    }

    $htmlObj->addLink("item_link", array(
        "link" => soyshop_get_item_detail_link($item),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addLink("item_cart_link", array(
        "link" => soyshop_get_cart_url(true) . "?a=add&count=1&item=" . $item->getId(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => ($item->isOrderable()),
    ));

    $htmlObj->addForm("item_cart_form", array(
        "method" => "post",
        "action" => soyshop_get_cart_url(true) . "?a=add&count=1&item=" . $item->getId(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "visible" => ($item->isOrderable()),
    ));

    $htmlObj->addForm("item_cart_default_form", array(
        "method" => "post",
        "action" => soyshop_get_cart_url(true) . "?a=add&count=1&item=" . $item->getId(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
    ));

    $htmlObj->addSelect("item_cart_select", array(
        "name" => "count",
        "options" => range(1,10),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    /** 子商品表示時の親商品のタグ **/
    $parent = new SOYShop_Item();
    $parentCategory = new SOYShop_Category();
    if(is_numeric($item->getType())) {
        try{
            $parent = $itemDao->getById($item->getType());
        }catch(Exception $e){
            //
        }

        if(is_numeric($parent->getCategory())){
            try{
                $parentCategory = $categoryDao->getById($parent->getCategory());
            }catch(Exception $e){
                //
            }
        }
    }

    $htmlObj->addLink("parent_link", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "link" => soyshop_get_item_detail_link($parent)
    ));

    $htmlObj->addLabel("parent_name", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "text" => $parent->getOpenItemName()
    ));

    $htmlObj->addLabel("parent_code", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "text" => $parent->getCode(),
    ));

    $htmlObj->addLabel("parent_category_name", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "text" => $parentCategory->getOpenCategoryName(),
    ));

    $htmlObj->addLabel("parent_category_alias", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "text" => $parentCategory->getAlias(),
    ));

    $htmlObj->addLink("parent_category_link", array(
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "link" => soyshop_get_item_list_link($parent, $parentCategory),
    ));

    //cms:id="parent_small_image"とcms:id="parent_large_image"
    foreach(array("small", "large") as $tp){
        $img = soyshop_convert_file_path($parent->getAttribute("image_" . $tp), $parent);
        $key = "parent_" . $tp . "_image";
        $htmlObj->addImage($key, array(
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
            "src" => $img
        ));

        $htmlObj->addModel($key . "_show", array(
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
            "visible" => (strlen($img) > 0)
        ));
    }

    /*
     * "sumbit"だけど互換性のために残しておく
     */
    $htmlObj->addInput("item_cart_sumbit", array(
        "disabled" => ($item->getStock() == 0),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addLabel("item_alias", array(
        "text" => $item->getAlias(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
    ));

    //model
    $htmlObj->addModel("type_group", array(
        "visible" => ($item->getType() == SOYShop_Item::TYPE_GROUP || $item->getType() == SOYShop_Item::TYPE_DOWNLOAD_GROUP),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addModel("no_type_parent", array(
        "visible" => ($item->getType() != SOYShop_Item::TYPE_GROUP && $item->getType() != SOYShop_Item::TYPE_DOWNLOAD_GROUP),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->addModel("type_child", array(
        "visible" => ($item->isChild()),
        "soy2prefix" => SOYSHOP_SITE_PREFIX
    ));

    $htmlObj->createAdd("create_date", "DateLabel", array(
        "text" => $item->getCreateDate(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "defaultFormat" => "Y.m.d"
    ));

    $htmlObj->createAdd("update_date", "DateLabel", array(
        "text" => $item->getUpdateDate(),
        "soy2prefix" => SOYSHOP_SITE_PREFIX,
        "defaultFormat" => "Y.m.d"
    ));

    /* event SOY CMSから読み込んだ時はカスタムフィールドは表示できない様にする*/
    if(defined("DISPLAY_SOYSHOP_SITE") && DISPLAY_SOYSHOP_SITE){
        SOYShopPlugin::invoke("soyshop.item.customfield", array(
            "item" => $item,
            "htmlObj" => $htmlObj,
            "pageObj" => $obj,
        ));
    }
}
