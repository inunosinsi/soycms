<?php

function register_user_attribute($stmt){
	$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
	
	try{
		$attrs = $dao->getAll();
		if(!count($attrs)) return;
	}catch(Exception $e){
		return;
	}

	foreach($attrs as $attr){
		$stmt->execute(array(
			":user_id" => $attr->getUserId(),
			":user_field_id" => $attr->getFieldId(),
			":user_value" => $attr->getValue(),
		));
	}
}
