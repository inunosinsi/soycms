<?php
if(!defined("ACCESS_RESTRICTIONS_CONFIG_DIR")) define("ACCESS_RESTRICTIONS_CONFIG_DIR", SOY2::RootDir() . "config/ip/");

class CMSAccessRestrictionsUtil {

	const UNLOCK_KEY = "unlock";

	const MODE_PERMANENT = 0;	//永続化設定
	const MODE_TEMPORARY = 1;	//一時設定
	const LABEL_PERMANENT = "permanent";
	const LABEL_TEMPORARY = "temporary";

	const TEMPORARY_LIFE_TIME_HOUR = 2;	//一時設定ファイルの生存時間
	const TOKEN_LIFE_TIME_MINUTES = 30;	//トークンファイルの生存時間

	/**
	 * @return string
	 */
	public static function readConfig(int $mode=self::MODE_PERMANENT){
		$idx = ($mode == self::MODE_PERMANENT) ? self::LABEL_PERMANENT : self::LABEL_TEMPORARY;
		return (file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx)) ? file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx) : "";
	}

	/**
	 * @param string
	 */
	public static function saveConfig(int $mode=self::MODE_PERMANENT, string $ipAddrs=""){
		$ipAddrs = trim($ipAddrs);
		$idx = ($mode == self::MODE_PERMANENT) ? self::LABEL_PERMANENT : self::LABEL_TEMPORARY;
		
		if(!strlen($ipAddrs)){
			unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx);
			return;
		}

		$addrs = explode("\n", $ipAddrs);
		$list = array();
		foreach($addrs as $addr){
			$addr = trim($addr);
			if(!strlen($addr)) continue;	// @ToDo IPアドレスのチェックを行いたい IPv4とIPv6の両方
			
			$list[] = $addr;
		}
		
		if(count($list)){
			// 現在アクセス中のIPアドレスを付与する
			$list[] = $_SERVER["REMOTE_ADDR"];
			file_put_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx, implode("\n", array_unique($list)));
		}else{
			unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx);
		}
	}

	/**
	 * temporaryやtokenファイルの生存期間を調べて削除する
	 */
	public static function organizeConfigFiles(){
		
		//if(filemtime(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TEMPORARY) > strtotime("+".self::TEMPORARY_LIFE_TIME_HOUR." hour")){
		//unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TEMPORARY);
		$files = soy2_scandir(ACCESS_RESTRICTIONS_CONFIG_DIR);
		if(!count($files)) return;

		foreach($files as $f){
			switch($f){
				case self::LABEL_PERMANENT:
				case "readme":
				case ".gitignore":
					// 何もしない
					break;
				case self::LABEL_TEMPORARY:	//ファイルの最終作成日時を調べて、作成してから2時間以上経過した場合はファイルを削除
					if(filemtime(ACCESS_RESTRICTIONS_CONFIG_DIR.$f) < strtotime("-".self::TEMPORARY_LIFE_TIME_HOUR." hour")){
						unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$f);
					}
					break;
				default:	// トークンファイルは30分で削除
					if(filemtime(ACCESS_RESTRICTIONS_CONFIG_DIR.$f) < strtotime("-".self::TOKEN_LIFE_TIME_MINUTES." min")){
						unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$f);
					}
			}
		}
	}

	/**
	 * IPアドレスによるアクセス制限を調べる
	 * @return bool
	 */
	public static function checkAllowIpAddress(){		
		// IPアドレスの設定用ファイルがなければ必ずtrue
		if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_PERMANENT) && !file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TEMPORARY)) return true;

		$remoteAddr = $_SERVER["REMOTE_ADDR"];

		foreach(array(self::LABEL_TEMPORARY, self::LABEL_PERMANENT) as $lab){
			if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$lab)) continue;
			
			$ipAddrs = explode("\n", file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$lab));
			if(!count($ipAddrs)) continue;
			
			foreach($ipAddrs as $addr){
				$addr = trim($addr);
				if(!strlen($addr)) continue;

				if($addr == $remoteAddr) return true;
			}
		}

		return false;
	}

	/**
	 * @param string
	 * @return bool
	 */
	public static function sendMailWithToken(string $userId){
		try{
			$user = SOY2DAOFactory::create("admin.AdministratorDAO")->getByUserId($userId);
		}catch(Exception $e){
			$user = new Administrator();
		}
		$mailAddress = $user->getEmail();
		if(!strlen($mailAddress) || !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mailAddress)) return false;

		// メールの設定がない
		if(!strlen(SOY2LogicContainer::get("logic.mail.MailConfigLogic")->get()->getFromMailAddress())) return false;

		// keyとtokenを作成する
		$key = substr(md5(time()), 0, 10);
		$token = md5($key.$mailAddress);

		// keyとtokenからファイルを作成する
		file_put_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$key, $token);

		$body = "下記のURLをクリックすると、一時的に現在アクセス中のIPアドレスからのアクセスを許可します。\n";
		$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
		$body .= $http."://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]."?".$key."=".$token;

		// メールを送信する
		SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail($mailAddress, SOYCMS_CMS_NAME . " IPアドレス設定", $body);
	}

	/**
	 * @param array
	 * @return bool
	 */
	public static function checkIsToken(array $params){
		if(!count($params)) return false;

		$keys = array_keys($params);
		$files = soy2_scandir(ACCESS_RESTRICTIONS_CONFIG_DIR);
		$key = "";
		foreach($keys as $k){
			if(is_numeric(array_search($k, $files))){
				$key = $k;
				break;
			}
		}
		if(!strlen($key)) return false;
		
		$token = file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$key);		
		if($token == $params[$key]){
			unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$key);
			return true;
		}
		
		return false;
	}

	/**
	 * IPアドレスの一時的にアクセスできるIPアドレスを設定する
	 */
	public static function setTemporaryConfig(){
		$ip = $_SERVER["REMOTE_ADDR"];
			
		$addrs = CMSAccessRestrictionsUtil::readConfig(self::MODE_TEMPORARY);
		$addrs .= "\n".$ip;
		CMSAccessRestrictionsUtil::saveConfig(self::MODE_TEMPORARY, trim($addrs));

		// メールを送信する
		SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail(
			SOY2LogicContainer::get("logic.mail.MailConfigLogic")->get()->getFromMailAddress(), 
			SOYCMS_CMS_NAME . " IPアドレス設定", 
			"IPアドレス : ".$ip."からのアクセスが一時的に許可されました。\n"
		);
	}
}