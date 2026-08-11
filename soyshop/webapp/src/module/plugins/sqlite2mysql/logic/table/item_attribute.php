<?php

function register_item_attribute($stmt){
	$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

	try{
		$attrs = $dao->getAll();
		if(!count($attrs)) return;
	}catch(Exception $e){
		return;
	}

	foreach($attrs as $attr){
		$stmt->execute(array(
			":item_id" => $attr->getItemId(),
			":item_field_id" => $attr->getFieldId(),
			":item_value" => $attr->getValue(),
			":item_extra_values" => $attr->getExtraValues()
		));
	}
}
