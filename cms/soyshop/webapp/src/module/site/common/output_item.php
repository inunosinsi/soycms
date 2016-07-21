<?php
SOYShopPlugin::load("soyshop.item.customfield");

/**
 * 商品情報を出力
 * テンプレートに記述しない
 */
function soyshop_output_item($htmlObj, SOYShop_Item $item, $obj=null){

	$htmlObj->addLabel("id", array(
		"text" => $item->getId(),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	//商品名
	$htmlObj->addLabel("item_name", array(
		"text" => $item->getOpenItemName(),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	//グループの場合の処理
	if($item->getType() == SOYShop_Item::TYPE_GROUP){
		$type = (method_exists($obj, "getSortType")) ? $obj->getSortType() : "item_code";
		$order = (method_exists($obj, "getSortOrder") && $obj->getSortOrder() == 1) ? $type . " desc" : $type . " asc";
		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
		));
		$childItems = $logic->getChildItems($item->getId(), $order);
	}
	if(!$htmlObj instanceof SOYShop_ChildItemListComponent){
		$htmlObj->createAdd("child_item_list", "SOYShop_ChildItemListComponent", array(
			"list" => ($item->getType() == SOYShop_Item::TYPE_GROUP) ? $childItems : array(),
			"soy2prefix" => "block"
		));
	}

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
		"visible" => ($item->getStock() > 0),
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
		"text" => $categoryObj->getName(),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));
	
	$htmlObj->addLink("category_link", array(
		"link" => soyshop_get_item_list_link($categoryObj),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));
	
	$imageSmall = soyshop_convert_file_path($item->getAttribute("image_small"), $item);
	$htmlObj->addImage("item_small_image", array(
		"src" => $imageSmall,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (strlen($imageSmall) > 0)
	));
	$htmlObj->addLink("item_small_image_link", array(
		"link" => $imageSmall,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (strlen($imageSmall) > 0)
	));

	$imageLarge = soyshop_convert_file_path($item->getAttribute("image_large"), $item);
	$htmlObj->addImage("item_large_image", array(
		"src" => $imageLarge,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (strlen($imageLarge) > 0)
	));

	$htmlObj->addLink("item_large_image_link", array(
		"link" => $imageLarge,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (strlen($imageLarge) > 0)
	));


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
		"visible" => ($item->getType() == SOYShop_Item::TYPE_GROUP),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$htmlObj->addModel("no_type_parent", array(
		"visible" => ($item->getType() != SOYShop_Item::TYPE_GROUP),
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
?>