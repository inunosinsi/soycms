<?php

function soyshop_search_field_result($html, $htmlObj){
    $obj = $htmlObj->create("soyshop_search_field_result", "HTMLTemplatePage", array(
        "arguments" => array("soyshop_search_field_result", $html)
    ));

	$users = array();

    SOY2::import("util.SOYShopPluginUtil");
    if(SOYShopPluginUtil::checkIsActive("user_custom_search_field") && isset($_GET["u_search"])){
		$logic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.SearchLogic");
		$logic->setIsProfileDisplay(true);
		$users = $logic->search(null, 1, 15);
    }

	SOY2::import("base.site.classes.SOYShop_UserListComponent");
	$obj->createAdd("user_list", "SOYShop_UserListComponent", array(
		"soy2prefix" => "block",
		"list" => $users
	));

    $obj->display();
}
