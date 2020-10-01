<?php

class ButtonSocialUtil{

	const PLUGIN_ID = "ButtonSocial";
	const PLUGIN_KEY = "ogimage_field";

	public static function getDetailUrlAndTitle($obj,$entryId){

		//ブログページだった場合
		//$objはBlogPage_EntryListなど
		if(property_exists($obj, "entryPageUri")){
			$uri = ltrim($obj->entryPageUri,"/");
		}else{
			$uri = "";
		}

		SOY2::import("util.UserInfoUtil");
		$url = UserInfoUtil::getSiteURLBySiteId("") . $uri;

		if(!isset($entryId) || !is_numeric($entryId)) return array($url, "");

		try{
			$entry = SOY2DAOFactory::create("cms.EntryDAO")->getById($entryId);
		}catch(Exception $e){
			$entry = new Entry();
		}

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

	private static function _convertTitle($obj, $format){
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

	public static function getAttr($entryId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		if(!is_numeric($entryId)) return new EntryAttribute();

		try{
			return $dao->get($entryId, self::PLUGIN_KEY);
		}catch(Exception $e){
			$attr = new EntryAttribute();
			$attr->setEntryId($entryId);
			$attr->setFieldId(self::PLUGIN_KEY);
			return $attr;
		}
	}

	public static function saveAttr(EntryAttribute $attr){
		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");

		if(strlen($attr->getValue())){
			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		//高速化をはかる為、オブジェクトを削除
		}else{
			try{
				$dao->delete($attr->getEntryId(), ButtonSocialUtil::PLUGIN_KEY);
			}catch(Exception $e){
				//
			}
		}
	}
}
