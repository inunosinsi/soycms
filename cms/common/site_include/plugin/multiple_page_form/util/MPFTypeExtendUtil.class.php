<?php

class MPFTypeExtendUtil {

	//ページのクラスファイルを格納するディレクトリを取得
	public static function getPageDir(){
		return self::_pageDir();
	}


	public static function getPageClassList(){
		$files = self::_scanDir();
		if(!count($files)) return $files;

		$list = array();
		foreach($files as $file){
			if(strpos($file, ".class.php")){
				$filename = trim(substr($file, strrpos($file, "/")), "/");
				$filename = str_replace(".class.php", "", $filename);
				$list[] = $filename;
			}
		}
		return $list;
	}

	private static function _pageDir(){
		//公開側と管理画面側で使用する関数が異なる
		if(defined("_SITE_ROOT_")){
			$dir = _SITE_ROOT_ . "/";
		}else{
			$dir = UserInfoUtil::getSiteDirectory();
		}

		$dir .= ".multiPageForm/";
		self::_createDir($dir);

		$dir .= "page/";
		self::_createDir($dir);

		return $dir;
	}

	private function _createDir($dir){
		if(!file_exists($dir)) mkdir($dir);
		if(!file_exists($dir . ".htaccess")) {
			file_put_contents($dir . ".htaccess", "Deny from all");
		}
	}

	private static function _scanDir(){
		return soy2_scanfiles(self::_pageDir());
	}
}
