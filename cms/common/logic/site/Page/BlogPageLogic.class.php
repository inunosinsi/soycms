<?php

class BlogPageLogic extends SOY2LogicBase{

    function getBlogPageList(){

    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	$pages = $dao->getByPageType(Page::PAGE_TYPE_BLOG);

    	foreach($pages as $key => $value){
    		$pages[$key] = $value->getTitle();
    	}

    	return $pages;

    }

    function get(){
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	return $dao->get();
    }

	function getBlogPageUriByLabelId(int $labelId){
		$list = self::_getBlogPageUriListCorrespondingToBlogLabelId();
		if(!isset($list[$labelId])) return "";
		$uris = $list[$labelId];
		return (isset($uris[0])) ? $uris[0] : "";	//一番最初の値を返す
	}

	/**
	 * blogLabelIdに対応したblog_uriの一覧を返す
	 */
	private function _getBlogPageUriListCorrespondingToBlogLabelId(){
		static $list;
		if(is_null($list)){
			$list = SOY2DAOFactory::create("cms.BlogPageDAO")->getBlogPageUriListCorrespondingToBlogLabelId();
		}
		return $list;
	}
}
