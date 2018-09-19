<?php
function soycms_entry_calendar($html, $page){

	$obj = $page->create("entry_calendar", "HTMLTemplatePage", array(
		"arguments" => array("entry_calendar", $html)
	));

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/entry_calendar.active")){
		$y = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
		$m = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");
		//$m = 8;	//debug

		//ブログIDを取得
		preg_match('/<!--.*cms:blog="(.*)".*\/-->/', $html, $tmp);
		if(isset($tmp[1]) && is_numeric($tmp[1])){
			$logic = SOY2Logic::createInstance("site_include.plugin.entry_calendar.logic.EntryCalendarLogic", array("blogId" => $tmp[1]));

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



			$obj->createAdd("date_list", "EntryCalendarComponent", array(
				"soy2prefix" => "c_block",
				"list" => $dateList,
				"year" => $y,
				"month" => $m,
				"entries" => $logic->getEntryList($y, $m),
				"entryPageUrl" => $logic->getEntryPageUrl()
			));
		}
	}

	$obj->display();
}

class EntryCalendarComponent extends HTMLList {

	private $year;
	private $month;
	private $entries;
	private $entryPageUrl;

	function populateItem($d){
		$dd = (isset($d) && is_numeric($d) && $d > 0) ? $d : "";

		//日付
		$this->addLabel("day", array(
			"soy2prefix" => "cms",
			"text" => $dd
		));

		//曜日を調べて、土(6)だったら、<tr>で敷居を付ける
		$this->addModel("next_week", array(
			"soy2prefix" => "cms",
			"visible" => (is_numeric($dd) && date("w", mktime(0, 0, 0, $this->month, $dd, $this->year)) == 6)
		));

		$this->createAdd("entry_list", "EntryCalendarEntryList", array(
			"soy2prefix" => "c_block",
			"list" => (is_numeric($dd) && isset($this->entries[$dd])) ? $this->entries[$dd] : array(),
			"link" => self::convertUrlOnModuleBlogParts($this->entryPageUrl)
		));
	}

	function setYear($year){
		$this->year = $year;
	}
	function setMonth($month){
		$this->month = $month;
	}
	function setEntries($entries){
		$this->entries = $entries;
	}

	function setEntryPageUrl($entryPageUrl){
		$this->entryPageUrl = $entryPageUrl;
	}

	private function convertUrlOnModuleBlogParts($url){
		static $siteUrl;
		if(is_null($siteUrl)){
			if(defined("SOYCMS_SITE_ID")){
				$siteId = SOYCMS_SITE_ID;
			}else{
				//SOY CMSの場合
				if(defined("_SITE_ROOT_")){
					$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
				}else{
					$siteId = UserInfoUtil::getSite()->getSiteId();
				}
			}

			$old = CMSUtil::switchDsn();
			try{
				$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
			}catch(Exception $e){
				$site = new Site();
			}
			CMSUtil::resetDsn($old);
			$siteUrl = "/";
			if(!$site->getIsDomainRoot()) $siteUrl .= $site->getSiteId() . "/";
		}
		return $siteUrl . $url;
	}
}

class EntryCalendarEntryList extends HTMLList {

	private $link;

	function populateItem($entry){
		$link = $this->link . "/" . rawurlencode($entry->getAlias());

		$this->createAdd("entry_id", "CMSLabel" ,array(
			"soy2prefix" => "cms",
			"text" => $entry->getId(),
		));

		$this->createAdd("title", "CMSLabel", array(
			"soy2prefix" => "cms",
			"html" => "<a href=\"" . $link . "\">" . htmlspecialchars($entry->getTitle(), ENT_QUOTES, "UTF-8") . "</a>",
		));

		$this->createAdd("title_plain", "CMSLabel" ,array(
			"soy2prefix" => "cms",
			"text" => $entry->getTitle(),
		));

		$this->addLink("entry_link", array(
			"soy2prefix" => "cms",
			"link" => $link
		));

		//カスタムフィールド
		CMSPlugin::callEventFunc('onEntryOutput', array("entryId" => $entry->getId(), "SOY2HTMLObject" => $this, "entry" => $entry));
	}

	function setLink($link){
		$this->link = $link;
	}
}
