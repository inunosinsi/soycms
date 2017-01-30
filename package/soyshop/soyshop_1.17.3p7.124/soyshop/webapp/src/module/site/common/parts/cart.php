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
	
	$count = 0;
	foreach($items as $item){
		$count = $count + (int)$item->getItemCount();
	}
	$obj->addLabel("item_total", array(
		"text" => $count,
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
			"text" => number_format($entity->getItemPrice()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_count", array(
			"text" => number_format($entity->getItemCount()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_total_price", array(
			"text" => number_format($entity->getTotalPrice()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}
}
}
?>