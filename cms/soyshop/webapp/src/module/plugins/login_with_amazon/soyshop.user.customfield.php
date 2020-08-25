<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class LoginWithAmazonUserCustomSearchField extends SOYShopUserCustomfield{

	private $dbLogic;

	function getForm($app, $userId){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$amazonId = SOY2Logic::createInstance("module.plugins.login_with_amazon.logic.LoginWithAmazonLogic")->getAmazonIdByUserId($userId);
			if(isset($amazonId)){
				return array(array("name" => "アマゾンID", "form" => $amazonId));
			}
		}
	}

	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}
	function hasError($param){}
	function confirm($app){}
	function register($app, $userId){}
}
SOYShopPlugin::extension("soyshop.user.customfield","login_with_amazon","LoginWithAmazonUserCustomSearchField");
