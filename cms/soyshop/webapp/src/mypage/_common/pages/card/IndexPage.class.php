<?php

//クレジットカードの更新の為のページ
class IndexPage extends MainMyPagePageBase{

	function doPost(){
		SOYShopPlugin::load("soyshop.mypage.card");
		$res = SOYShopPlugin::invoke("soyshop.mypage.card", array(
			"mode" => "post"
		))->getResult();

		if($res){
			$this->jump("card?success");
		}else{
			$this->jump("card?failed");
		}
	}

	function __construct(){
		// @ToDo ログイン以外でもメール文面からでも開けるようにしたい
		$this->checkIsLoggedIn(); //ログインチェック

		parent::__construct();

		DisplayPlugin::toggle("success", isset($_GET["success"]));
		DisplayPlugin::toggle("form", !isset($_GET["success"]));

		if(isset($_GET["success"])){
			//何もしない
		}else{
			SOYShopPlugin::load("soyshop.mypage.card");
			$this->addLabel("option_page", array(
				"html" => SOYShopPlugin::display("soyshop.mypage.card", array(
					"mode" => "form"
				))
			));
		}

	}
}
