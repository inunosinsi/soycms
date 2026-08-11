<?php

class StorageLogic extends SOY2LogicBase{

	//登録可能な拡張子 txtや画像は一旦なし
	private $allowExtensions = array(
								".zip" => "application/zip",
								".epub" => "application/epub+zip",
								".pdf" => "application/pdf",
								".mp3" => "audio/mpeg",
								".mp4" => "application/mp4"
							);

	private $storageDao;

	function __construct(){
		SOY2::import("module.plugins.store_user_folder.domain.SOYShop_UserStorageDAO");
		$this->storageDao = SOY2DAOFactory::create("SOYShop_UserStorageDAO");
	}

	function getFiles($userId){
		try{
			$files = $this->storageDao->getByUserId($userId);
		}catch(Exception $e){
			return array();
		}

		$dir = self::getDirectoryByUserId($userId);

		$list = array();
		foreach($files as $file){
			if(file_exists($dir . "/" . $file->getFileName())){
				$list[] = $file;
			}
		}

		return $list;
	}

	function getFileByToken($token){
		try{
			return $this->storageDao->getByToken($token);
		}catch(Exception $e){
			return null;
		}
	}

	function upload($files, $userId){
		$dir = self::getDirectoryByUserId($userId);

		SOYShopPlugin::load("soyshop.upload.image");
		for($i = 0; $i < count($files["type"]); $i++){
			if(!$files["error"][$i] && in_array($files["type"][$i], $this->allowExtensions)){
				/** @ToDo ファイル名のチェック **/
				$fname = $files["name"][$i];

				$new = SOYShopPlugin::invoke("soyshop.upload.image", array(
					"mode" => "storage",
					"pathinfo" => pathinfo($fname)
				))->getName();

				if(isset($new)) $fname = $new;

				//半角英数字かチェックする
//				if (preg_match("/^[0-9A-Za-z%&+\-\^_`{|}~.]+$/", $fname)){
					$dest_name = $dir . "/" . $fname;

					//iconsディレクトリの中にすでにファイルがないかチェックする
					if(!file_exists($dest_name)){
						//ファイルの移動が失敗していないかどうかをチェック
						if(@move_uploaded_file($files["tmp_name"][$i], $dest_name) === false){
							continue;
						}
					}

					//データベースに登録する
					$obj = new SOYShop_UserStorage();
					$obj->setUserId($userId);
					$obj->setFileName($fname);
					$obj->setToken(md5(time().$userId.$files["tmp_name"][$i].rand(0,65535)));

					try{
						$this->storageDao->insert($obj);
					}catch(Exception $e){
						var_dump($e);
					}
//				}
			}
		}
	}

	function uploadWithFilePath($filepath, $userId){
		if(!file_exists($filepath)) return false;

		$fname = trim(trim(substr($filepath, strrpos($filepath, "/")), "/"));
		$dir = self::getDirectoryByUserId($userId);

		SOYShopPlugin::load("soyshop.upload.image");
		$new = SOYShopPlugin::invoke("soyshop.upload.image", array(
			"mode" => "storage",
			"pathinfo" => pathinfo($fname)
		))->getName();

		if(isset($new)) $fname = $new;
		$dest_name = $dir . "/" . $fname;

		//iconsディレクトリの中にすでにファイルがないかチェックする
		if(!file_exists($dest_name)){
			if(rename($filepath, $dest_name)){
				//データベースに登録する
				$obj = new SOYShop_UserStorage();
				$obj->setUserId($userId);
				$obj->setFileName($fname);
				$obj->setToken(md5($filepath.rand(0,65535)));

				try{
					$this->storageDao->insert($obj);
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}

		return true;
	}

	/**
	 * ファイルをダウンロードする
	 * @param object SOYShop_UserStorage file, string filepath
	 */
	function downloadFile(SOYShop_UserStorage $file){
		$filePath = self::getDirectoryByUserId($file->getUserId()) . "/" . $file->getFileName();
		$contentType = self::getContentType($file->getFileName());

		header("Cache-Control: public");
		header("Pragma: public");
		header("Content-Type: " . $contentType . ";");
    	header("Content-Disposition: attachment; filename=" . basename($file->getFileName()));
    	header("Content-Length: " . filesize($filePath));

		flush();
		while(ob_get_level()){
			ob_end_clean();
		}

		$handle = fopen($filePath, 'rb');
		while ( $handle !== false && !feof($handle) && ! connection_aborted() ){
			echo fread($handle, 4096);
			flush();
		}
		fclose($handle);
	}

	/**
	 * ダウンロードするファイルのcontent-typeを取得する
	 * @param string filename
	 * @return string extenstion
	 */
	function getContentType($fileName){
		$extension = strtolower(substr($fileName, strrpos($fileName, ".")));
		return (isset($this->allowExtensions[$extension])) ? $this->allowExtensions[$extension] : "application/octet-stream";
	}

	private function getDirectoryByUserId($userId){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/user/";
		if(!is_dir($dir)) mkdir($dir);

		$dir .= $userId;
		if(!is_dir($dir)) mkdir($dir);
		return $dir;
	}
}
