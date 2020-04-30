<?php

class CacheLogic extends SOY2LogicBase {

	//バージョンが異なる場合はtrue developingは無視
	function checkCacheVersion(){
		if(SOYCMS_VERSION == "developing") return false;
		$res = false;
		if($dh = opendir(SOY2HTMLConfig::CacheDir())) {
			while(($f = readdir($dh)) !== false) {
				if(strpos($f, ".") === 0 || is_dir($f)) continue;
				$res = (strpos($f, SOYCMS_VERSION) === false);
				break;
			}
			closedir($dh);
	    }
		return $res;
	}

	function clearCache(){
		$root = dirname(SOY2::RootDir());
		CMSUtil::unlinkAllIn($root . "/admin/cache/");
		CMSUtil::unlinkAllIn($root . "/soycms/cache/");
		CMSUtil::unlinkAllIn($root . "/soyshop/cache/");
		CMSUtil::unlinkAllIn($root . "/app/cache/", true);

		$sites = SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getSiteList();
		foreach($sites as $site){
			CMSUtil::unlinkAllIn($site->getPath() . ".cache/", true);
		}

		//リダイレクト
		//SOY2PageController::jump("");
	}
}
