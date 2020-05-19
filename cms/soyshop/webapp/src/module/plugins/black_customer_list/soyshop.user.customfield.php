<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class BlackCustomerListUserCustomfield extends SOYShopUserCustomfield{

	function register($app, $userId){

		//管理画面側でのみ実行
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$checked = (isset($_POST["BlackCustomerList"])) ? 1 : 0;
			self::getLogic()->save($userId, $checked);
		}
	}

	function getForm($app, $userId){

		//管理画面側でのみ表示
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){

			$attr = self::getLogic()->getAttribute($userId);

			if(!is_null($attr->getValue()) && $attr->getValue() == 1){
				$form = "<label><input type=\"checkbox\" name=\"BlackCustomerList\" value=\"1\" checked>ブラックリストに登録する</label>";
			}else{
				$form = "<label><input type=\"checkbox\" name=\"BlackCustomerList\" value=\"1\">ブラックリストに登録する</label>";
			}


			return array(array(
				"name" => "ブラック顧客",
				"form" => $form
			));
		}


		return array();
	}

	function order($userId){
		$attr = self::getLogic()->getAttribute($userId);
		if(!is_null($attr->getValue()) && $attr->getValue() == 1){
			return array(array(
				"name" => "ブラックリスト",
				"value" => "ブラックリストに登録されています。",
				"style" => "font-weight:bold;color:#FF0000;"
			));
		}

		return array();
	}

	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.black_customer_list.logic.BlackListLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.user.customfield", "black_customer_list", "BlackCustomerListUserCustomfield");
