<?php

class HistoryIdCacheLogic extends SOY2LogicBase {

	function readCache(){
		$path = self::_cacheDir() . "history.json";
		if(!file_exists($path)) return array();
		return json_decode(file_get_contents($path), true);
	}

	function saveCache($ids){
		file_put_contents(self::_cacheDir() . "history.json", json_encode($ids));
	}

	function removeCache(){
		$path = self::_cacheDir() . "history.json";
		if(file_exists($path)) unlink($path);
	}

	private function _cacheDir(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".cache/mypage/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "edit/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}
}
