<?php
SOY2::import("site_include.block._common.BlockEntryListComponent");
class EntryCalendarComponent extends HTMLList {

	private $year;
	private $month;
	private $entries;
	private $entryPageUrl;
	private $blogPageId;

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

		$this->createAdd("entry_list", "BlockEntryListComponent", array(
			"soy2prefix" => "c_block",
			"list" => (is_numeric($dd) && isset($this->entries[$dd])) ? $this->entries[$dd] : array(),
			"articlePageUrl" => self::_convertUrlOnModuleBlogParts($this->entryPageUrl),
			"blogPageId" => $this->blogPageId,
			"isStickUrl" => true
		));
	}

	private function _convertUrlOnModuleBlogParts($url){
		static $siteUrl;
		if(is_null($siteUrl)){
			$siteUrl = "/";
			if(!SOYCMS_IS_DOCUMENT_ROOT) $siteUrl .= SOYCMS_SITE_ID . "/";
		}
		return $siteUrl . $url;
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

	function setBlogPageId($blogPageId){
		$this->blogPageId = $blogPageId;
	}
}
