<?php

class SOYInquiryConnectorUtil {

	public static function getInquiryPageList($isTitle=false){
		try{
			$pages = SOY2DAOFactory::create("cms.PageDAO")->getByPageType(Page::PAGE_TYPE_APPLICATION);
		}catch(Exception $e){
			$pages = array();
		}
		if(!count($pages)) return array();

		$list = array();
		foreach($pages as $page){
			$obj = $page->getPageConfigObject();
			if(!property_exists($obj, "applicationId") || $obj->applicationId != "inquiry") continue;
			if($isTitle){
				$list[$page->getId()] = $page->getTitle() . " (/" . $page->getUri() . ")";
			}else{
				$list[$page->getId()] = $page->getUri();
			}
		}
		return $list;
	}
}
