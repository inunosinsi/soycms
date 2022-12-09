<?php
SOY2HTMLFactory::importWebPage("message.IndexPage");
class CompletePage extends IndexPage{

    function __construct() {
    	$this->checkIsLoggedIn(); //ログインチェック
    	$this->clearPostToSession("front_message_post");

    	parent::__construct();

    	$this->addLink("message_link", array(
    		"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri() . "/message"
    	));
    }
}
