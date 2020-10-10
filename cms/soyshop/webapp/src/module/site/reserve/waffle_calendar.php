<?php
function soyshop_waffle_calendar($html, $page){
	//ログインチェック
	if(!isset($_GET["output_css_mode"])){
		if(!MyPageLogic::getMyPage()->getIsLoggedin()){
			//ログイン後のリダイレクト用に今見ているページのURLを取得する
			soyshop_redirect_login_form("r=redirect");
		}

		if(!isset($_GET["idx"]) || !is_numeric($_GET["idx"])){
			header("Location:" . soyshop_get_cart_url(true));
		}

		//予防接種二回目の有無　無しでも必ず下記のパラーメータはある
		if(!isset($_GET["secMode"]) || !is_numeric($_GET["secMode"])){
			header("Location:" . soyshop_get_cart_url(true));
		}

		//ユーザIDがない場合は表示しない
		if(!isset($_GET["userId"]) || !is_numeric($_GET["userId"])){
			header("Location:" . soyshop_get_cart_url(true));
		}
	}

	$obj = $page->create("soyshop_waffle_calendar", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_waffle_calendar", $html)
	));

	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");

	//直近の空き予約を調べる
	if(!isset($_GET["y"]) || !isset($_GET["m"])){
		list($year, $month) = SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.Schedule.SmartScheduleLogic")->findLatestScheduleDate($year, $month);
	}

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

/**
	$obj->addForm("schedule_calendar_form", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"method" => "GET"
	));

	$obj->addSelect("schedule_year_select", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "y",
		"options" => range($year, $year + 1),
		"selected" => $year
	));

	$obj->addSelect("schedule_month_select", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "m",
		"options" => range(1, 12),
		"selected" => $month
	));
**/

	SOY2::import("module.plugins.calendar_expand_waffle.util.WaffleCalendarUtil");
	$GLOBALS["reserved_schedules"] = WaffleCalendarUtil::getReservationStatus($year, $month);

	$url = soyshop_get_page_url($page->getPageObject()->getUri());

	//リンク
	$obj->addModel("prev_month", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (mktime(0, 0, 0, $prevM + 1, 1, $prevY) - 1 > time())
	));

	$obj->addLink("prev_month_link", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"link" => $url . "?y=" . $prevY . "&m=" . $prevM . "&idx=" . $_GET["idx"] . "&secMode=" . $_GET["secMode"] . "&userId=" . $_GET["userId"]
	));

	$obj->addModel("next_month", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => true	//常にtrue
	));

	$obj->addLink("next_month_link", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"link" => $url . "?y=" . $nextY . "&m=" . $nextM . "&idx=" . $_GET["idx"] . "&secMode=" . $_GET["secMode"] . "&userId=" . $_GET["userId"]
	));

	$obj->addLabel("caption", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $year . "年" . $month . "月"
	));

	if(!defined("RESERVE_CALENDAR_MODE")) define("RESERVE_CALENDAR_MODE", "bootstrap");

	//商品分だけタグを生成する
	$itemIdList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getRegisteredItemIdsOnLabel();
	foreach($itemIdList as $itemId){

		//ボタンの非同期設定 syncがtrueで同期 falseで非同期
		$sync = true;
		if(preg_match('/block:id=\"calendar_' . $itemId . '\".*cms:async=\"(.*?)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1]) && (int)$tmp[1] === 1) $sync = false;
		}

		$obj->addLabel("calendar_" . $itemId, array(
			"soy2prefix" => "block",
			"html" => (soyshop_get_item_object($itemId)->isPublished()) ? SOY2Logic::createInstance("module.plugins.calendar_expand_waffle.logic.View.CalendarLogic", array("itemId" => $itemId, "sync" => $sync))->build($year, $month) : ""
		));
	}

	//非同期の場合のjsファイルの挿入
	// $obj->addLabel("async_js", array(
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX,
	// 	"html" => "\n" . file_get_contents(SOY2::RootDir() . "module/plugins/reserve_calendar/js/async.js")
	// ));

	// $obj->addInput("async_cart_url", array(
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX,
	// 	"type" => "hidden",
	// 	"value" => soyshop_get_cart_url(true),
	// 	"attr:id" => "reserve_calendar_cart_url"
	// ));

	if(count($itemIdList)){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
