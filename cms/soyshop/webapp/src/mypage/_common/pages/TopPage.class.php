<?php

class TopPage extends MainMyPagePageBase{

    function __construct() {
		$this->checkIsLoggedIn(); //ログインチェック
		if(!class_exists("SOYShopPluginUtil")) SOY2::import("util.SOYShopPluginUtil");

		parent::__construct();

		$user = $this->getUser();

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		$this->addModel("is_profile_display", array(
			"visible" => ($user->getIsProfileDisplay() == SOYShop_User::PROFILE_IS_DISPLAY && strlen($user->getProfileId()) > 0),
		));

		$this->addLink("profile_link", array(
			"link" => soyshop_get_mypage_url() . "/profile/" . $user->getProfileId(),
		));

    	$this->addLink("order_link", array(
    		"link" => soyshop_get_mypage_url() . "/order"
    	));

    	$this->addLink("edit_link", array(
    		"link" => soyshop_get_mypage_url() . "/edit"
    	));

    	$this->addLink("address_link", array(
    		"link" => soyshop_get_mypage_url() . "/address"
    	));

    	$this->addLink("password_link", array(
			"link" => soyshop_get_mypage_url() . "/password",
		));

		//ダウンロード販売モードの時に表示する
    	$this->addModel("is_download", array(
    		"visible" => (SOYShopPluginUtil::checkIsActive("download_assistant"))
    	));

    	$this->addLink("download_link", array(
    		"link" => soyshop_get_mypage_url() . "/download"
    	));

    	$this->addModel("is_review", array(
    		"visible" => (SOYShopPluginUtil::checkIsActive("item_review"))
    	));

    	$this->addLink("review_link", array(
    		"link" => soyshop_get_mypage_url() . "/review"
    	));

    	$this->addLink("mail_log_link", array(
			"link" => soyshop_get_mypage_url() . "/mail",
		));

    	$this->addModel("is_point", array(
			"visible" => (SOYShopPluginUtil::checkIsActive("common_point_base")),
		));
		$this->addLink("point_link", array(
			"link" => soyshop_get_mypage_url() . "/point",
		));

		$this->addModel("is_pay_jp", array(
			"visible" => (SOYShopPluginUtil::checkIsActive("payment_pay_jp"))
		));
		$this->addLink("pay_jp_link", array(
			"link" => soyshop_get_mypage_url() . "/credit/payJp",
		));

		$this->addLink("withdraw_link", array(
			"link" => soyshop_get_mypage_url() . "/withdraw",
		));
		$this->addActionLink("logout_link", array(
			"link" => soyshop_get_mypage_url() . "/logout",
		));

    	$this->addLink("top_link", array(
    		"link" => soyshop_get_site_url()
    	));
    }
}
