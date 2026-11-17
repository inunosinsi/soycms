<?php
class SyncTemplateUtil {

	const TARGET_DIR = "export";

	/**
	 * 公開側でも呼び出せるようにしておく
	 */
	public static function getSiteDirectory(){
		if(defined("_SITE_ROOT_")){
			return _SITE_ROOT_."/";
		}elseif(class_exists("UserInfoUtil")){
			return UserInfoUtil::getSiteDirectory();
		}else{
			return "";
		}
	}

	/**
	 * 出力先ディレクトリにアクセス拒否の.htaccessを作る
	 * @return bool
	 */
	public static function checkDirectory(){
		$targetDir = self::targetDirectory();
		if(!file_exists($targetDir)) return false;

		if(is_dir($targetDir) && is_writable($targetDir)){
			if(!file_exists($targetDir."/.htaccess")){
				file_put_contents($targetDir."/.htaccess", "Deny from all");
				return true;
			}
		}
		return false;
	}

	/**
	 * ディレクトリを作る
	 */
	public static function makeDirectory(){
		$targetDir = self::targetDirectory();
		if(file_exists($targetDir)) return true;

		if(mkdir($targetDir)){
			chmod($targetDir, 0777);
			if(is_writable($targetDir)){
				file_put_contents($targetDir."/.htaccess", "Deny from all");
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * 書き出されているファイルを削除する
	 */
	public static function deleteFiles(){
		$export_dir = self::targetDirectory();
		if(is_dir($export_dir)){
			foreach(scandir($export_dir) as $file){
				if($file[0] == ".") continue;
				unlink($export_dir."/".$file);
			}
		}
	}

	/**
	 * 出力ディレクトリを削除する
	 */
	public static function deleteDirectory(){
		$export_dir = self::targetDirectory();
		@unlink($export_dir."/.htaccess");
		@rmdir($export_dir);
	}

	/**
	 * 出力ディレクトリが削除可能か
	 */
	public static function isDirDeletable(){
		$export_dir = self::targetDirectory();
		$parent_dir = dirname($export_dir);
		return is_dir($export_dir) && is_writable($parent_dir) && is_executable($parent_dir);
	}

	/**
	 * @return int
	 */
	public static function countExportedFiles(){
		$count = 0;
		$export_dir = SyncTemplateUtil::targetDirectory();
		if(is_dir($export_dir)){
			foreach(scandir($export_dir) as $file){
				if($file[0] == ".") continue;
				$count++;
			}
		}
		return $count;
	}

	/**
	 * @return string
	 */
	public static function targetDirectory(){
		return self::getSiteDirectory().self::TARGET_DIR;
	}
}
