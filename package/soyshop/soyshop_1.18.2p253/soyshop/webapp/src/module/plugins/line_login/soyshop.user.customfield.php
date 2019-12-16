<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class LINELoginUserCustomSearchField extends SOYShopUserCustomfield{

	private $dbLogic;

	function getForm($app, $userId){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$lineId = SOY2Logic::createInstance("module.plugins.line_login.logic.LINELoginLogic")->getLINEIdByUserId($userId);
			if(isset($lineId)){
				return array(array("name" => "LINE ID", "form" => $lineId));
			}
		}
	}

	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}
	function hasError($param){}
	function confirm($app){}
	function register($app, $userId){}
}
SOYShopPlugin::extension("soyshop.user.customfield","line_login","LINELoginUserCustomSearchField");
