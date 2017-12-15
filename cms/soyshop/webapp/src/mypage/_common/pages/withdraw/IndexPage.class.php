<?php

class IndexPage extends MainMyPagePageBase{

    function __construct() {
		$this->checkIsLoggedIn(); //ログインチェック

    	parent::__construct();

    	$this->addModel("error", array(
    		"visible" => (isset($_GET["error"]))
    	));

    	$this->addActionLink("remove_link", array(
    		"link" => soyshop_get_mypage_url() . "/withdraw/remove",
    	));
    }
}
