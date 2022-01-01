<?php

class ButtonSocialUtil{

	const PLUGIN_ID = "ButtonSocial";
	const PLUGIN_KEY = "ogimage_field";

	public static function getDetailUrlAndTitle($obj, int $entryId){

		//ブログページだった場合
		//$objはBlogPage_EntryListなど
		if(property_exists($obj, "entryPageUri")){
			$uri = ltrim($obj->entryPageUri,"/");
		}else{
			$uri = "";
		}

		SOY2::import("util.UserInfoUtil");
		$url = UserInfoUtil::getSiteURLBySiteId("") . $uri;

		if(!is_numeric($entryId)) return array($url, "");

		$entry = soycms_get_entry_object($entryId);
		$url .= rawurlencode($entry->getAlias());
		return array($url, $entry->getTitle());
	}

	public static function getPageUrl(){
		static $url;
		if(is_null($url)){
			if(isset($_SERVER['HTTPS'])){
				$url = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			}else{
				$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			}
		}
		return $url;
	}

	public static function getTitle($obj){
		static $title;
		if(is_null($title)) $title = self::_convertTitle($obj, self::_getTitleFormat($obj));	//タイトルフォーマットを取得
		return $title;
	}

	private static function _convertTitle($obj, string $format=""){
		//ページのタイトルを取得
		$title = $obj->page->getTitle();

		//サイト名を取得する
		$format = str_replace("%PAGE%", $title, $format);
		$format = str_replace("%BLOG%", $title, $format);
		$format = str_replace("%SITE%", $obj->siteConfig->getName(), $format);

		//ブログページのタイトルフォーマットの置換処理
		if(get_class($obj) == "CMSBlogPage"){
			SOY2::import('site_include.CMSBlogPage');
			switch($obj->mode){
				case CMSBlogPage::MODE_ENTRY:
					//エントリ名を取得
					return str_replace("%ENTRY%", $obj->entry->getTitle(), $format);
				case CMSBlogPage::MODE_MONTH_ARCHIVE:
					$format = str_replace("%YEAR%", $obj->year, $format);
					$format = str_replace("%MONTH%", $obj->month, $format);
					return str_replace("%DAY%", $obj->day, $format);
				case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
					//カテゴリ名を取得
					return str_replace("%CATEGORY%", $obj->label->getCaption(), $format);
			}
		}

		return $format;
	}

	//タイトルフォーマットを取得
	private static function _getTitleFormat($obj){
		switch(get_class($obj)){
			case "CMSPage":
			case "CMSApplicationPage":
				return $obj->page->getPageTitleFormat();
			case "CMSBlogPage":
				switch($obj->mode){
					case CMSBlogPage::MODE_TOP:
						return $obj->page->getTopTitleFormat();
					case CMSBlogPage::MODE_ENTRY:
						return $obj->page->getEntryTitleFormat();
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
						return $obj->page->getMonthTitleFormat();
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						return $obj->page->getCategoryTitleFormat();
					default:
						//
				}
				break;
			default:
				//
		}

		return "";
	}

	public static function getAttr(int $entryId){
		return soycms_get_entry_attribute_object($entryId, self::PLUGIN_KEY);
	}

	public static function saveAttr(EntryAttribute $attr){
		soycms_save_entry_attribute_object(($attr));		
	}
}
