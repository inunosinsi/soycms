<?php

class AspUserPreRegistrationPage extends WebPage{

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspUtil");
	}

	function execute(){
		parent::__construct();

		$admin = AspUtil::get();
		if(!strlen($admin->getEmail())) {//メールアドレスがない場合は確認画面を表示させない
			header("location:" . AspUtil::getPageUri());
		}

		AspUtil::clear();
	}
}
