<?php

class ReadEntryCountUtil {

	public static function aggregate(int $entryId){
		$obj = self::_getReadEntryCountObject($entryId);
		$cnt = (int)$obj->getCount();
		$cnt++;
		$obj->setCount($cnt);

		try{
			self::_dao()->insert($obj);
		}catch(Exception $e){
			try{
				self::_dao()->update($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	public static function getReadEntryCountByEntryId(int $entryId){
		return (int)self::_getReadEntryCountObject($entryId)->getCount();
	}

	private static function _getReadEntryCountObject(int $entryId){
		try{
			return self::_dao()->getByEntryId($entryId);
		}catch(Exception $e){
			$obj = new ReadEntryCount();
			$obj->setEntryId($entryId);
			return $obj;
		}
	}

	public static function getBlogPageList(){
		try{
			$pages = SOY2DAOFactory::create("cms.BlogPageDAO")->get();
		}catch(Exception $e){
			return array();
		}
		if(!count($pages)) return array();

		$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
		$old = CMSUtil::switchDsn();
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		CMSUtil::resetDsn($old);
		$url = "/";
		if(!$site->getIsDomainRoot()) $url .= $site->getSiteId() . "/";

		$list = array();
		foreach($pages as $pageId => $page){
			if(strlen($page->getUri())){
				$list[$page->getBlogLabelId()] = $url . $page->getUri() . "/" . $page->getEntryPageUri() . "/";
			//ページのURLが空文字の場合
			}else{
				$list[$page->getBlogLabelId()] = $url . $page->getEntryPageUri() . "/";
			}
		}
		return $list;
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::imports("site_include.plugin.read_entry_count.domain.*");
			$dao = SOY2DAOFactory::create("ReadEntryCountDAO");
		}
		return $dao;
	}
}
