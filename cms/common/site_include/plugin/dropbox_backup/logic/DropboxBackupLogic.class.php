<?php

class DropboxBackupLogic extends SOY2LogicBase {

	function __construct(){}

	function backup($siteId){

		//取り急ぎSQLite版のみ
		include_once(SOY2::RootDir() . "config/db/sqlite.php");
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::User(ADMIN_DB_USER);
		SOY2DAOConfig::Pass(ADMIN_DB_PASS);
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			return;
		}

		//キャッシュの削除
		SOY2::import("util.CMSUtil");
		CMSUtil::unlinkAllIn($site->getPath() . ".cache/", true);

		SOY2DAOConfig::Dsn($site->getDataSourceName());
		SOY2::import("domain.cms.DataSets");
		$token = DataSets::get("dropbox_backup.token", null);
		if(!strlen($token)) return;

		//バックアップの処理
		$backupDir = $site->getPath() . ".dropbox/";
		if(!file_exists($backupDir)){
			mkdir($backupDir);
		}

		$zipFilePath = $backupDir . $siteId . ".zip";
		if(class_exists("ZipArchive")){	//ZipArchiveを利用
			self::_zipDir($site->getPath(), $zipFilePath);
		}else{	//linuxのコマンドを利用
			$cmd = "zip -r '" . $zipFilePath . "' " . $site->getPath();
			$res = exec($cmd);
			if(!$res) return;
		}


		//Dropboxにzipファイルを転送する
		$ch = curl_init();

	    $headers = array(
	        'Authorization: Bearer ' . $token, //取得したアクセストークン
	        'Content-Type: application/octet-stream',
	        'Dropbox-API-Arg: {"path":"/backup/' . basename($zipFilePath) . '", "mode":{".tag":"overwrite"}}', //上書きモード
	    );

    	$fp = fopen($zipFilePath, "rb");

	    $opts = array(
	        CURLOPT_URL => "https://content.dropboxapi.com/2/files/upload",
	        CURLOPT_HTTPHEADER => $headers,
	        CURLOPT_POST => true,
	        CURLOPT_POSTFIELDS => fread($fp, filesize($zipFilePath)),
	        CURLOPT_RETURNTRANSFER => true,
	    );

    	curl_setopt_array($ch, $opts);

    	$res = curl_exec($ch);
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// @ToDo エラーログ等はどうする？
	    // if (!curl_errno($ch) && $httpCode == "200") {
		//
		// } else {
		//
	    // }

		fclose($fp);
		curl_close($ch);

		//Dropboxにzipファイルを送信したら、ファイルを削除する
		unlink($zipFilePath);
	}

	private function _zipDir($dir, $file, $root=""){
	    $zip = new ZipArchive();
	    $res = $zip->open($file, ZipArchive::CREATE);

	    if($res){
	        // $rootが指定されていればその名前のフォルダにファイルをまとめる
	        if($root != "") {
	            $zip->addEmptyDir($root);
	            $root .= DIRECTORY_SEPARATOR;
	        }

	        $baseLen = mb_strlen($dir);

	        $iterator = new RecursiveIteratorIterator(
	            new RecursiveDirectoryIterator(
	                $dir,
	                FilesystemIterator::SKIP_DOTS
	                |FilesystemIterator::KEY_AS_PATHNAME
	                |FilesystemIterator::CURRENT_AS_FILEINFO
	            ), RecursiveIteratorIterator::SELF_FIRST
	        );

	        $list = array();
	        foreach($iterator as $pathname => $info){
	            $localpath = $root . mb_substr($pathname, $baseLen);

	            if( $info->isFile() ){
	                $zip->addFile($pathname, $localpath);
	            } else {
	                $res = $zip->addEmptyDir($localpath);
	            }
	        }

	        $zip->close();
	    } else {
	        return false;
	    }
	}
}
