<?php
function soyshop_parts_cart($html, $page){

	$obj = $page->create("soyshop_parts_cart", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_cart", $html)
	));
	
	if(!defined("SOYSHOP_CURRENT_CART_ID")) define("SOYSHOP_CURRENT_CART_ID", soyshop_get_cart_id());

	$cart = CartLogic::getCart();
	$cartIsEmpty = ("Complete" == $cart->getAttribute("page") || count($cart->getItems()) < 1);

	$obj->addModel("empty_cart", array(
		"visible" => $cartIsEmpty,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addModel("full_cart", array(
		"visible" => !$cartIsEmpty,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	// cms:id="item_list"を使わない場合はSOYShop_CartItemListComponentを読み込まない
	if(!is_numeric(strpos($html, "cms:id=\"item_list\""))){
		SOY2::import("module.site.common._component.SOYShop_CartItemListComponent");
		$obj->createAdd("item_list", "SOYShop_CartItemListComponent", array(
			"list" => $cart->getItems(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}

	$obj->addLabel("item_total", array(
		"text" => soy2_number_format($cart->getOrderItemCount()),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addLabel("cart_total", array(
		"text" => soy2_number_format($cart->getItemPrice()),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addLabel("cart_total_included_tax", array(
		"text" => soy2_number_format($cart->getItemPriceIncludedTax()),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addLink("cart_link", array(
		"link" => soyshop_get_cart_url(),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	/** ポイント関連 **/

	//カート内のポイント表示
	$point = 0;
	SOY2::import("util.SOYShopPluginUtil");
    if(SOYShopPluginUtil::checkIsActive("common_point_base")){
		if(SOYShopPluginUtil::checkIsActive("common_point_grant")){
			$point = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic", array("cart" => $cart))->getTotalPointOnCart();
		} else if(SOYShopPluginUtil::checkIsActive("fixed_point_grant")){
			$point = SOY2Logic::createInstance("module.plugins.fixed_point_grant.logic.FixedPointGrantLogic", array("cart" => $cart))->getTotalPointOnCart();
		}
	}

	//カート内にポイントがある場合
	$obj->addModel("is_point_in_cart", array(
		"visible" => ($point > 0),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addModel("no_point_in_cart", array(
		"visible" => ($point === 0),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addLabel("point_in_cart", array(
		"text" => $point,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->display();
}