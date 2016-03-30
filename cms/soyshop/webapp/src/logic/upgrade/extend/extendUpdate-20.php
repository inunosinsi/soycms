<?php
SOY2::import("util.SOYShopPluginUtil");
if(SOYShopPluginUtil::checkIsActive("common_point_base") && !SOYShopPluginUtil::checkIsActive("common_point_grant")){
	$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
	$logic->prepare();
	$logic->installModule("common_point_grant");
}
?>