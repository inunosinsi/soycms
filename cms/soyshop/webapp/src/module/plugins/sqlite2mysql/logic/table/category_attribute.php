<?php

function register_category_attribute($stmt){
	$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");

	try{
		$attrs = $dao->getAll();
		if(!count($attrs)) return;
	}catch(Exception $e){
		return;
	}

	foreach($attrs as $attr){
		$stmt->execute(array(
			":category_id" => $attr->getCategoryId(),
			":category_field_id" => $attr->getFieldId(),
			":category_value" => $attr->getValue(),
			":category_value2" => $attr->getValue2()
		));
	}
}
