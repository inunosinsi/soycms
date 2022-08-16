<?php
function soycms_entry_calendar($html, $page){

	$obj = $page->create("entry_calendar", "HTMLTemplatePage", array(
		"arguments" => array("entry_calendar", $html)
	));

	//プラグインがアクティブかどうか？
	if(CMSPlugin::activeCheck("entry_calendar")){
		$y = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
		$m = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");
		//$m = 8;	//debug

		//ブログIDを取得
		preg_match('/<!--.*cms:blog="(.*)".*\/-->/', $html, $tmp);
		if(isset($tmp[1]) && is_numeric($tmp[1])){
			$blogId = (int)$tmp[1];
			$logic = SOY2Logic::createInstance("site_include.plugin.entry_calendar.logic.EntryCalendarLogic", array("blogId" => $blogId));

			$dateList = array();

			//今月の最終日を取得
			$lastDate = mktime(0, 0, 0, $m + 1, 1, $y) - 1;
			$last = (int)date("j", $lastDate);

			//最初の日の曜日を取得
			$firstW = date("w", mktime(0, 0, 0, $m, 1, $y));

			//前月の最終日を取得
			$prevMonthLastD = date("j", mktime(0, 0, 0, $m, 1, $y) - 1);
			for($i = $firstW - 1; $i >= 0; $i--){
				$dateList[] = ($prevMonthLastD - $i) * -1;
			}

			//日付の範囲を取得。前の月の日付の場合はマイナスを付けておく
			for($i = 1; $i <= $last; $i++){
				$dateList[] = $i;
			}

			//来月のはじめの日付にもマイナスの値を付けておく
			$lastW = date("w", $lastDate);
			if($lastW < 6){
				for($i = 1; $i <= 6 - $lastW; $i++){
					$dateList[] = -1 * $i;
				}
			}

			//表示月の表示
			$obj->addLabel("current_year", array(
				"soy2prefix" => "cms",
				"text" => $y
			));

			$obj->addLabel("current_month", array(
				"soy2prefix" => "cms",
				"text" => $m
			));

			$link = $_SERVER["REQUEST_URI"];
			if(isset($_SERVER["QUERY_STRING"]) && strlen($_SERVER["QUERY_STRING"])){
				$link = str_replace("?" . $_SERVER["QUERY_STRING"], "", $link);
			}

			$pm = $m - 1;
			if($pm < 1) {
				$py = $y - 1;
				$pm = 12;
			}else{
				$py = $y;
			}

			//前の月
			$obj->addLink("prev_month_link", array(
				"soy2prefix" => "cms",
				"link" => $link . "?y=" . $py . "&m=" . $pm
			));

			$nm = $m + 1;
			if($nm > 12){
				$ny = $y + 1;
				$nm = 1;
			}else{
				$ny = $y;
			}

			$obj->addLink("next_month_link", array(
				"soy2prefix" => "cms",
				"link" => $link . "?y=" . $ny . "&m=" . $nm
			));

			$list = $logic->getEntryList($y, $m);
			$entries = array();
			foreach($list as $arr){
				if(!count($arr)) continue;
				foreach($arr as $entry){
					$entries[] = $entry;
				}
			}
			
			CMSPlugin::callEventFunc('onEntryListBeforeOutput', array("entries" => &$entries));
			unset($entries);

			SOY2::import("site_include.plugin.entry_calendar.component.EntryCalendarComponent");
			$obj->createAdd("date_list", "EntryCalendarComponent", array(
				"soy2prefix" => "c_block",
				"list" => $dateList,
				"year" => $y,
				"month" => $m,
				"entries" => $list,
				"entryPageUrl" => $logic->getEntryPageUrl(),
				"blogPageId" => $blogId
			));
		}
	}

	$obj->display();
}
