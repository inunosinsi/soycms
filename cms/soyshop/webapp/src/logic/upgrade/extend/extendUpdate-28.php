<?php
$userAttrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");

try{
	$userAttrDao->executeUpdateQuery("DELETE FROM soyshop_user_attribute where user_id IS NULL");
}catch(Exception $e){
	//
}
?>