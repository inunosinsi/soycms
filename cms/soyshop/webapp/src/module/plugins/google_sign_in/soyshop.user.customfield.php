<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class GoogleSignInUserCustomSearchField extends SOYShopUserCustomfield{

	function getForm($app, $userId){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$googleId = SOY2Logic::createInstance("module.plugins.google_sign_in.logic.SignInLogic")->getGoogleIdByUserId($userId);
			if(isset($googleId)){
				return array(array("name" => "Google ID", "form" => $googleId));
			}
		}
	}

	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}
	function hasError($param){}
	function confirm($app){}
	function register($app, $userId){}
}
SOYShopPlugin::extension("soyshop.user.customfield","google_sign_in","GoogleSignInUserCustomSearchField");
