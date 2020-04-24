<?php
SOY2HTMLFactory::importWebPage("message.detail.IndexPage");
class CompletePage extends IndexPage{

	private $id;

	function __construct($args) {
		$this->checkIsLoggedIn(); //ログインチェック

		$this->id = $args[0];

		parent::__construct();

		$this->clearPostToSession("front_message_post");

    	parent::__construct();

    	$this->addLink("message_link", array(
    		"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri() . "/message/detail/" . $this->id
    	));
	}
}
?>
