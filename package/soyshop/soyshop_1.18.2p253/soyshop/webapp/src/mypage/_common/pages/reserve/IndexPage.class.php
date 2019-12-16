<?php

class IndexPage extends MainMyPagePageBase{

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//予約カレンダーを有効にしていて、bootstrapテンプレートを使用している時のみ表示
		if(!SOYShopPluginUtil::checkIsActive("reserve_calendar")) $this->jumpToTop();
		if(soyshop_get_mypage_id() != "bootstrap") $this->jumpToTop();

		parent::__construct();

		$user = $this->getUser();
        $this->addLabel("user_name", array(
            "text" => $user->getName()
        ));

		$year = date("Y");
		$month = date("n");

		$this->addLabel("calendar", array(
			"html" => SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.Reserve.CalendarLogic", array("userId" => $user->getId()))->build($year, $month)
		));

		$this->addLabel("smart_js", array(
			"html" => file_get_contents(SOY2::RootDir() . "module/plugins/calendar_expand_smart/js/smart.js")
		));
	}
}
