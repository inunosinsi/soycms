<?php

class AccessRestrictionSiteOnLoad extends SOYShopSiteOnLoadAction{

	function onLoad($page){
		if(!SOYSHOP_APPLICATION_MODE){	//アプリケーションモードではない場合
			SOY2::import("module.plugins.access_restriction.util.AccessRestrictionUtil");
			$cnf = AccessRestrictionUtil::getPageDisplayConfig();
			if(isset($cnf[SOYSHOP_PAGE_ID]) && (int)$cnf[SOYSHOP_PAGE_ID] === AccessRestrictionUtil::ON){
				//鍵を持っていなければ、404NotFoundのページに遷移させる
				if(!AccessRestrictionUtil::checkBrowser()){
					header("Location:" . self::_get404NotFoundPageUrl());
					exit;
				}

				//鍵の更新
				AccessRestrictionUtil::releaseBrowser();
				AccessRestrictionUtil::registerBrowser();
			}
		}
	}

	private function _get404NotFoundPageUrl(){
		try{
			$page = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByUri(SOYSHOP_404_PAGE_MARKER);
		}catch(Exception $e){
			//404NotFoundページがなければ、何処かのページの遷移させる　@ToDo アクセス制限をかけたいページがトップページだった場合は機能しない
			$page = new SOYShop_Page();
		}
		return soyshop_get_page_url($page->getUri());
	}
}

SOYShopPlugin::extension("soyshop.site.onload", "access_restriction", "AccessRestrictionSiteOnLoad");
