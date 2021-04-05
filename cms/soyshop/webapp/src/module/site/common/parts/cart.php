<?php
function soyshop_parts_cart($html, $page){

	$obj = $page->create("soyshop_parts_cart", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_cart", $html)
	));

	if(!defined("SOYSHOP_CURRENT_CART_ID")){
		define("SOYSHOP_CURRENT_CART_ID", soyshop_get_cart_id());
	}

	$cart = CartLogic::getCart();
	$items = $cart->getItems();
	$cartIsEmpty = (count($items) < 1 || "Complete" == $cart->getAttribute("page"));

	$obj->addModel("empty_cart", array(
		"visible" => $cartIsEmpty,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addModel("full_cart", array(
		"visible" => !$cartIsEmpty,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->createAdd("item_list", "SOYShop_CartItemList", array(
		"list" => $items,
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addLabel("item_total", array(
		"text" => $cart->getOrderItemCount(),
		"soy2prefix" => SOYSHOP_SITE_PREFIX
	));

	$obj->addLabel("cart_total", array(
		"text" => number_format($cart->getItemPrice()),
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

if(!class_exists("SOYShop_CartItemList")){

class SOYShop_CartItemList extends HTMLList{

	protected function populateItem($entity){
		$this->addLabel("item_name", array(
			"text" => $entity->getItemName(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format($entity->getItemPrice()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_count", array(
			"text" => soy2_number_format($entity->getItemCount()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format($entity->getTotalPrice()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}
}
}
