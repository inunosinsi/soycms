<?php

class ApplicationLogic extends SOY2LogicBase{

	function getApplications(){
		$applicationDir = self::getApplicationDir();

		$files = scandir($applicationDir);

		if(! self::checkIsInstalledApplication()){
			return array();
		}

		$applications = array();

		foreach($files as $file){
			if($file[0] == ".")continue;
			if($file == "base")continue;

			if( $info = self::importApplicationIniFile($file) ){
				$applications[$file] = $info;
			}
		}

		return $applications;
	}

	private function getApplicationDir(){
		return str_replace("\\","/",dirname(SOY2::RootDir()) . "/app/webapp/");
	}

	/**
	 * アプリケーションを取得
	 * @throw Exception
	 */
	function getApplication($appId){
		if( $info = $this->importApplicationIniFile($appId) ){
			return $info;
		}else{
			throw new Exception("No Application");
		}
	}

	/**
	 * アプリケーションのINIファイルの内容を配列に変換する
	 */
	private function importApplicationIniFile($appId){
		$applicationDir = self::getApplicationDir();
		$iniFile = $applicationDir . "/" . $appId . "/application.ini";

		if(!is_readable($iniFile)) return false;

		$inis = parse_ini_file($iniFile);
		if(!$inis) return false;

		return array(
			"id"		  => $appId,
			"title"	   => isset($inis["title"])	   ? $inis["title"]	   : null,
			"description" => isset($inis["description"]) ? $inis["description"] : null,
			"version"	 => isset($inis["version"])	 ? $inis["version"]	 : null,
			//複数権限を使うかどうか（true/false）
			"useMultipleRole" => ( isset($inis["multiple_role"]) && $inis["multiple_role"] ),
			//権限設定を独自で持つときのURI（app/index.php/以降の部分）
			"customRoleUri" => ( isset($inis["custom_role_uri"]) && strlen($inis["custom_role_uri"]) ) ? $inis["custom_role_uri"] : "",
		);

	}

	/**
	 * アプリケーションがインストール済みか
	 */
	private function checkIsInstalledApplication(){
		$applicationDir = $this->getApplicationDir();

		$files = scandir($applicationDir);

		$flag = false;
		foreach($files as $file){
			if($file[0] == ".")continue;
			if($file == "base")continue;

			$flag = true;
		}

		return $flag;

	}

	/**
	 * ログイン可能なアプリケーションのリストを返します。
	 */
	function getLoginableApplications($userId){
		static $app_res;
		if(is_null($app_res)){
			$app_res = array();

			$applications = self::getApplications();

			if(is_array($applications) && count($applications)){
				$roles = SOY2DAOFactory::create("admin.AppRoleDAO")->getByUserId($userId);

				foreach($roles as $userId => $role){
					$appId = $role->getAppId();

					//SOY Appでサイト側のデータベースに初期化するもの
					if($appId == "inquiry" && defined("SOYINQUIRY_USE_SITE_DB") && SOYINQUIRY_USE_SITE_DB) continue;
					if($appId == "mail" && defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB) continue;

					//App管理者(1)、App操作者(2)共にログインできる
					if(isset($applications[$appId])&&$role->getAppRole() > 0){
						$app_res[$appId] = $applications[$appId];
					}
				}
			}
		}

		return $app_res;
	}

	function getLoginiableApplicationLists(){
		if(UserInfoUtil::isDefaultUser()){
			return self::getApplications();
		}else{
			return self::getLoginableApplications(UserInfoUtil::getUserId());
		}
	}
}
