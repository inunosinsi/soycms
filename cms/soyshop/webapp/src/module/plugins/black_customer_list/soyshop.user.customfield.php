<?php

class BlackCustomerListUserCustomfield extends SOYShopUserCustomfield{

	function register($app, int $userId){
		if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) return;

		//管理画面側でのみ実行
		$checked = (isset($_POST["BlackCustomerList"])) ? 1 : "";

		SOY2::import("module.plugins.black_customer_list.util.BlackCustomerListUtil");
		$attr = soyshop_get_user_attribute_object($userId, BlackCustomerListUtil::PLUGIN_ID);
		$attr->setValue($checked);
		soyshop_save_user_attribute_object($attr);
	}

	function getForm($app, int $userId){
		//管理画面側でのみ表示
		if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) return array();

		SOY2::import("module.plugins.black_customer_list.util.BlackCustomerListUtil");
		if(soyshop_get_user_attribute_value($userId, BlackCustomerListUtil::PLUGIN_ID, "int")){
			$form = "<label><input type=\"checkbox\" name=\"BlackCustomerList\" value=\"1\" checked>ブラックリストに登録する</label>";
		}else{
			$form = "<label><input type=\"checkbox\" name=\"BlackCustomerList\" value=\"1\">ブラックリストに登録する</label>";
		}

		return array(array(
			"name" => "ブラック顧客",
			"form" => $form
		));
	}

	function order(int $userId){
		SOY2::import("module.plugins.black_customer_list.util.BlackCustomerListUtil");
		if(soyshop_get_user_attribute_value($userId, BlackCustomerListUtil::PLUGIN_ID, "int")){
			return array(array(
				"name" => "ブラックリスト",
				"value" => "ブラックリストに登録されています。",
				"style" => "font-weight:bold;color:#FF0000;"
			));
		}

		return array();
	}
}
SOYShopPlugin::extension("soyshop.user.customfield", "black_customer_list", "BlackCustomerListUserCustomfield");
