<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class FacebookLoginUserCustomSearchField extends SOYShopUserCustomfield{

	function getForm($app, $userId){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$facebookId = SOY2Logic::createInstance("module.plugins.facebook_login.logic.FBLoginLogic")->getFacebookIdByUserId($userId);
			if(isset($facebookId)){
				return array(array("name" => "Facebook ID", "form" => $facebookId));
			}
		}
	}

	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}
	function hasError($param){}
	function confirm($app){}
	function register($app, $userId){}
}
SOYShopPlugin::extension("soyshop.user.customfield","facebook_login","FacebookLoginUserCustomSearchField");
