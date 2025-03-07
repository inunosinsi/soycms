<?php

class ReadEntryCountUtil {

	const GLOBAL_INDEX = "read_entry_count_acceleration_key";

	/**
	 * @param int
	 */
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

	/**
	 * @param array
	 */
	public static function setReadEntryCountByEntryIds(array $entryIds){
		$list = (count($entryIds)) ? self::_dao()->getCountListByEntryIds($entryIds) : array();
		if(count($list)){
			foreach($list as $entryId => $cnt){
				$GLOBALS[self::GLOBAL_INDEX][$entryId] = $cnt;
			}
		}
	}

	/**
	 * @param int
	 * @return int
	 */
	public static function getReadEntryCountByEntryId(int $entryId){
		return (isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) ? (int)$GLOBALS[self::GLOBAL_INDEX][$entryId] : (int)self::_getReadEntryCountObject($entryId)->getCount();
	}

	/**
	 * @param int
	 * @return ReadEntryCount
	 */
	private static function _getReadEntryCountObject(int $entryId){
		try{
			return self::_dao()->getByEntryId($entryId);
		}catch(Exception $e){
			$obj = new ReadEntryCount();
			$obj->setEntryId($entryId);
			return $obj;
		}
	}

	/**
	 * @return array
	 */
	public static function getBlogPageList(){
		static $list;
		if(is_array($list)) return $list;

		$list = array();
		try{
			$pages = soycms_get_hash_table_dao("blog_page")->get();
		}catch(Exception $e){
			return array();
		}
		if(!count($pages)) return array();

		$url = "/";
		if(!soycms_check_is_root_site_by_frontcontroller()) $url .= soycms_get_site_id_by_frontcontroller() . "/";

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
	/**
	 * @param int
	 * @return array
	 */
	public static function getBlogPageListByPageId(int $selectedPageId=0){
		$list = array();
		try{
			$page = soycms_get_hash_table_dao("blog_page")->getById($selectedPageId);
		}catch(Exception $e){
			return array();
		}

		$url = "/";
		if(!soycms_check_is_root_site_by_frontcontroller()) $url .= soycms_get_site_id_by_frontcontroller() . "/";

		if(strlen($page->getUri())){
			$list[$page->getBlogLabelId()] = $url . $page->getUri() . "/" . $page->getEntryPageUri() . "/";
		//ページのURLが空文字の場合
		}else{
			$list[$page->getBlogLabelId()] = $url . $page->getEntryPageUri() . "/";
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
