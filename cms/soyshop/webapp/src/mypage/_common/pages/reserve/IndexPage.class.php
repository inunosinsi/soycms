<?php

class IndexPage extends MainMyPagePageBase{

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//予約カレンダーを有効にしていて、bootstrapテンプレートを使用している時のみ表示。MYPAGE_EXTEND_BOOTSTRAPはpage.phpで定数の設定を行う
		if(!SOYShopPluginUtil::checkIsActive("reserve_calendar")) $this->jumpToTop();
		if(soyshop_get_mypage_id() != "bootstrap" && (!defined("MYPAGE_EXTEND_BOOTSTRAP") || !MYPAGE_EXTEND_BOOTSTRAP)) $this->jumpToTop();

		parent::__construct();

		$user = $this->getUser();
        $this->addLabel("user_name", array(
            "text" => $user->getName()
        ));

		$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
		$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");

		//昨月
		$prevY = $year;
		$prevM = $month - 1;
		if($prevM < 1){
			$prevY -= 1;
			$prevM = 12;
		}

		//次の月も調べる
		$nextM = $month + 1;
		$nextY = $year;
		if($nextM > 12){
			$nextM = 1;
			$nextY += 1;
		}

		$url = soyshop_get_mypage_url() . "/reserve";

		//リンク
		$this->addModel("prev_month", array(
			"visible" => (mktime(0, 0, 0, $prevM + 1, 1, $prevY) - 1 > time())
		));

		$this->addLink("prev_month_link", array(
			"link" => $url . "?y=" . $prevY . "&m=" . $prevM
		));

		$this->addModel("next_month", array(
			"visible" => true	//常にtrue
		));

		$this->addLink("next_month_link", array(
			"link" => $url . "?y=" . $nextY . "&m=" . $nextM
		));

		$this->addLabel("caption", array(
			"text" => $year . "年" . $month . "月"
		));

		$this->addLabel("calendar", array(
			"html" => SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.Reserve.CalendarLogic", array("userId" => $user->getId()))->build($year, $month)
		));

		$this->addLabel("smart_js", array(
			"html" => file_get_contents(SOY2::RootDir() . "module/plugins/calendar_expand_smart/js/smart.js")
		));
	}
}
