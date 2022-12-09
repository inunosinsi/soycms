<?php

class CacheLogic extends SOY2LogicBase {

	//バージョンが異なる場合はtrue developingは無視
	function checkCacheVersion(){
		if(SOYSHOP_VERSION == "SOYSHOP_VERSION") return false;
		$res = false;
		if($dh = opendir(SOY2HTMLConfig::CacheDir())) {
			while(($f = readdir($dh)) !== false) {
				if(strpos($f, ".") === 0 || is_dir($f)) continue;
				$res = (strpos($f, SOYSHOP_VERSION) === false);
				break;
			}
			closedir($dh);
	    }

		return $res;
	}

	function clearCache(){
		//管理画面側のキャッシュ削除
		if($dh = opendir(SOY2HTMLConfig::CacheDir())) {
			while(($f = readdir($dh)) !== false) {
				if(strpos($f, ".") === 0 || is_dir($f)) continue;
				if(strpos($f, ".php")) unlink(SOY2HTMLConfig::CacheDir() . $f);
			}
			closedir($dh);
	    }

		//公開側のキャッシュ削除 SOYCMS側の機能を利用
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAdminDsn();
		SOY2::import("util.CMSUtil");
		CMSUtil::unlinkAllIn(SOYSHOP_SITE_DIRECTORY . ".cache/", true);
		SOYAppUtil::resetAdminDsn($old);

		//リダイレクト
		SOY2PageController::jump("");
	}
}
