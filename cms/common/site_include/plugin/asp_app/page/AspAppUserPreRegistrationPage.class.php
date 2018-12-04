<?php

class AspAppUserPreRegistrationPage extends WebPage{

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspAppUtil");
	}

	function execute(){
		parent::__construct();

		$admin = AspAppUtil::get();
		if(!strlen($admin->getEmail())) {//メールアドレスがない場合は確認画面を表示させない
			header("location:" . AspAppUtil::getPageUri());
		}

		AspAppUtil::clear();
	}
}
