<?php

class GeneratePasswordUtil {

	public static function getConfig(){
		return self::_getConfig();
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("generate_password.config", array(
			"password_strlen" => 12,
			"include_symbol" => 0,	//自動生成されるパスワードの文字列に記号を含めるか？
			"generate_pw_on_admin" => 0,			//管理画面側で生成したアカウントもパスワードの自動作成の対象にするか？
			"send_mail_on_admin" => 0,				//
			"insert_mail_text" => "受信したメールアドレスと下記のパスワードでマイページにログインすることが出来ます。\n##PASSWORD##\n"
		));
	}

	public static function saveConfig($values){
		$values["password_strlen"] = soyshop_convert_number($values["password_strlen"], 12);
		$values["include_symbol"] = (isset($values["include_symbol"])) ? (int)$values["include_symbol"] : 0;
		$values["generate_pw_on_admin"] = (isset($values["generate_pw_on_admin"])) ? (int)$values["generate_pw_on_admin"] : 0;
		$values["send_mail_on_admin"] = (isset($values["send_mail_on_admin"])) ? (int)$values["send_mail_on_admin"] : 0;
		SOYShop_DataSets::put("generate_password.config", $values);
	}

	//メールアドレスをハッシュ化して、パスワードを一時保管するファイルを作成する
	public static function saveAutoGeneratePassword($mail, $pw){
		file_put_contents(self::_backupDir() . self::_generateFileName($mail), $pw);
	}

	private static function _backupDir(){
		$cacheDir = SOYSHOP_SITE_DIRECTORY . ".cache/tmp_pw/";
		if(!file_exists($cacheDir) || !is_dir($cacheDir)) mkdir($cacheDir);
		return $cacheDir;
	}

	private static function _generateFileName($mail){
		return substr(md5($mail), 0 , 8) . ".txt";
	}

	public static function buildPasswordMessage($mail){
		$pw = self::_getSavedAutoGeneratePassword($mail);
		if(!strlen($pw)) return "";
		$cnf = self::_getConfig();
		$tmp = (isset($cnf["insert_mail_text"])) ? $cnf["insert_mail_text"] : "";
		if(!strlen($tmp)) return "";

		if(strpos($tmp, "##ACCOUNT_ID##") !== false){
			$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mail);
			$tmp = str_replace("##ACCOUNT_ID##", $user->getAccountId(), $tmp);
		}

		if(strpos($tmp, "##PASSWORD##") !== false){
			$tmp = str_replace("##PASSWORD##", $pw, $tmp);
		}

		if(strpos($tmp, "##LOGIN_PAGE_URL##") !== false){
			$tmp = str_replace("##LOGIN_PAGE_URL##", soyshop_get_mypage_url(true) . "/login", $tmp);
		}

		return $tmp;
	}

	private static function _getSavedAutoGeneratePassword($mail){
		$filepath = self::_backupDir() . self::_generateFileName($mail);
		if(!file_exists($filepath)) return "";
		$pw = file_get_contents($filepath);
		unlink($filepath);
		return $pw;
	}
}
