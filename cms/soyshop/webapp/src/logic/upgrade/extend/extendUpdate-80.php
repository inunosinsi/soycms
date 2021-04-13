<?php
$attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
try{
	$results = $attrDao->executeQuery("SELECT item_id, item_field_id FROM soyshop_item_attribute WHERE item_id IS NULL OR item_field_id IS NULL OR item_value IS NULL OR item_value = ''");
}catch(Exception $e){
	$results = array();
}

if(count($results)){
	foreach($results as $res){
		try{
			$attrDao->executeUpdateQuery("DELETE FROM soyshop_item_attribute WHERE item_id = :itemId AND item_field_id = :fieldId", array(":itemId" => $res["item_id"], ":fieldId" => $res["item_field_id"]));
		}catch(Exception $e){
			//
		}

	}
}

$attrDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
try{
	$results = $attrDao->executeQuery("SELECT category_id, category_field_id FROM soyshop_category_attribute WHERE category_id IS NULL OR category_field_id IS NULL OR category_value IS NULL OR category_value = ''");
}catch(Exception $e){
	$results = array();
}

if(count($results)){
	foreach($results as $res){
		try{
			$attrDao->executeUpdateQuery("DELETE FROM soyshop_category_attribute WHERE category_id = :categoryId AND category_field_id = :fieldId", array(":categoryId" => $res["category_id"], ":fieldId" => $res["category_field_id"]));
		}catch(Exception $e){
			//
		}

	}
}

$attrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
try{
	$results = $attrDao->executeQuery("SELECT user_id, user_field_id FROM soyshop_user_attribute WHERE user_id IS NULL OR user_field_id IS NULL OR user_value IS NULL OR user_value = ''");
}catch(Exception $e){
	$results = array();
}

if(count($results)){
	foreach($results as $res){
		try{
			$attrDao->executeUpdateQuery("DELETE FROM soyshop_user_attribute WHERE user_id = :userId AND user_field_id = :fieldId", array(":userId" => $res["user_id"], ":fieldId" => $res["user_field_id"]));
		}catch(Exception $e){
			//
		}

	}
}
unset($attrDao);
