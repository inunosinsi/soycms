<?php

class ExportPage extends WebPage{

    function __construct() {

    	//ログインチェック	ログインしていなければ強制的に止める
		if(!soyshop_admin_login()){
			echo "invalid plugin id";
			exit;
		}

		$plugin = (isset($_POST["plugin"])) ? $_POST["plugin"] : null;
		if(is_null($plugin)){
			echo "invalid plugin id";
			exit;
		}

		// @ToDo 予約状況　検索の仕組みをこのファイルで持つか？
		//$reserves = array();

		$plugin = soyshop_get_plugin_object($plugin);
		if(!is_null($plugin->getId())){
			SOYShopPlugin::load("soyshop.calendar.export", $plugin);
			SOYShopPlugin::invoke("soyshop.calendar.export", array(
				"mode" => "export"
			))->export(array());
		}

		exit;
    }
}
