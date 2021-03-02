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
		return rtrim($siteUrl . $url, "/") . "/";
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
