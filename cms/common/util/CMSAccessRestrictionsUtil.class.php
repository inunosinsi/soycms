<?php
if(!defined("ACCESS_RESTRICTIONS_CONFIG_DIR")) define("ACCESS_RESTRICTIONS_CONFIG_DIR", SOY2::RootDir() . "config/ip/");

class CMSAccessRestrictionsUtil {

	const UNLOCK_KEY = "unlock";

	const MODE_PERMANENT = 0;	//永続化設定
	const MODE_TEMPORARY = 1;	//一時設定
	const MODE_BLACKLIST = 2;
	const MODE_LOCKOUT = 3;
	const MODE_MALICIOUS = 4;
	const MODE_USER_AGENT = 5;	//ユーザーエージェント
	const LABEL_PERMANENT = "permanent";
	const LABEL_TEMPORARY = "temporary";
	const LABEL_BLACKLIST = "blacklist";
	const LABEL_LOCKOUT = "lockout";
	const LABEL_MALICIOUS = "malicious";	// 悪意のあるIPアドレスリスト
	const LABEL_TWO_FACTOR = "twofactor";	// 二段階認証の設定用
	const LABEL_IP_DIRECT_ACCESS = "ipdirect";	// IPアドレス直接打ち込みによるアクセス
	const LABEL_USER_AGENT = "useragent";	// ユーザーエージェント

	const TEMPORARY_LIFE_TIME_HOUR = 2;	//一時設定ファイルの生存時間
	const LOCKOUT_LIFE_TIME_HOUR = 1;	//一時設定ファイルの生存時間
	const TOKEN_LIFE_TIME_MINUTES = 30;	//トークンファイルの生存時間

	/**
	 * @param int
	 * @return string
	 */
	private static function _label(int $mode){
		switch($mode){
			case self::MODE_PERMANENT:
				return self::LABEL_PERMANENT;
			case self::MODE_TEMPORARY:
				return self::LABEL_TEMPORARY;
			case self::MODE_BLACKLIST:
				return self::LABEL_BLACKLIST;
			case self::MODE_LOCKOUT:
				return self::LABEL_LOCKOUT;
			case self::MODE_MALICIOUS:
				return self::LABEL_MALICIOUS;
			case self::MODE_USER_AGENT:
				return self::LABEL_USER_AGENT;
		}
	}

	/**
	 * @return string
	 */
	public static function readConfig(int $mode=self::MODE_PERMANENT){
		$idx = self::_label($mode);
		return (file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx)) ? file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx) : "";
	}

	/**
	 * @param string
	 */
	public static function saveConfig(int $mode=self::MODE_PERMANENT, string $ipAddrs=""){
		$ipAddrs = trim($ipAddrs);
		$idx = self::_label($mode);
		
		if(!strlen($ipAddrs)){
			if(file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx)) unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx);
			return;
		}

		$addrs = explode("\n", $ipAddrs);
		$list = array();
		switch($mode){
			case self::MODE_TEMPORARY:
			case self::MODE_LOCKOUT:
			case self::MODE_MALICIOUS:
				if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx)) touch(ACCESS_RESTRICTIONS_CONFIG_DIR.$idx);
				$ips = explode("\n", self::readConfig($mode));
				if(count($ips)){
					foreach($ips as $ip){
						$ip = trim($ip);
						if(!strlen($ip)) continue;
						$list[] = $ip;
					}
				}
				
				break;
			default:
				//
		}
		foreach($addrs as $addr){
			$addr = trim($addr);
			if(!strlen($addr)) continue;	// @ToDo IPアドレスのチェックを行いたい IPv4とIPv6の両方
			
			$list[] = $addr;
		}
		
		if(count($list)){
			if($mode == self::MODE_PERMANENT){
				// 現在アクセス中のIPアドレスを付与する
				$list[] = $_SERVER["REMOTE_ADDR"];
			}
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
			// ***.oldの形式のファイルは何もしない
			if(preg_match('/\.old$/', $f) === 1) continue;
		
			switch($f){
				case self::LABEL_PERMANENT:
				case self::LABEL_MALICIOUS:
				case self::LABEL_TWO_FACTOR:
				case self::LABEL_USER_AGENT:
				case "readme":
				case ".gitignore":
					// 何もしない
					break;
				case self::LABEL_TEMPORARY:	//ファイルの最終作成日時を調べて、作成してから2時間以上経過した場合はファイルを削除
					if(filemtime(ACCESS_RESTRICTIONS_CONFIG_DIR.$f) < strtotime("-".self::TEMPORARY_LIFE_TIME_HOUR." hour")){
						unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.$f);
					}
					break;
				case self::LABEL_LOCKOUT:	//ファイルの最終作成日時を調べて、作成してから1時間以上経過した場合はファイルを削除
					if(filemtime(ACCESS_RESTRICTIONS_CONFIG_DIR.$f) < strtotime("-".self::LOCKOUT_LIFE_TIME_HOUR." hour")){
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
		// 先にブラックリストを必ず調べる
		if(self::checkDenyIpAddress()) return false;

		// IPアドレスの設定用ファイルがなければ必ずtrue
		if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_PERMANENT) && !file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TEMPORARY)) return true;

		$remoteAddr = $_SERVER["REMOTE_ADDR"];

		$isIpv4 = (filter_var($remoteAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
		if(!$isIpv4){
			$_arr = explode(":", $remoteAddr);
			//固定部分を抽出する
			$remoteAddr = $_arr[0].":".$_arr[1].":".$_arr[2].":".$_arr[3].":";
		}

		// ホワイトリスト
		foreach(array(self::LABEL_TEMPORARY, self::LABEL_PERMANENT) as $lab){
			if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$lab)) continue;
			
			$ipAddrs = explode("\n", file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$lab));
			if(!count($ipAddrs)) continue;

			foreach($ipAddrs as $addr){
				$addr = trim($addr);
				if(!strlen($addr)) continue;

				// REMOTE_ADDRがIPv4の場合
				if($isIpv4){
					if($addr == $remoteAddr) return true;
				// REMOTE_ADDRがIPv6の場合
				}else{
					$res = strpos($addr, $remoteAddr);
					if(is_numeric($res) && (int)$res === 0) return true;	
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function checkDenyIpAddress(){
		$remoteAddr = $_SERVER["REMOTE_ADDR"];
		
		// ブラックリスト
		foreach(array(self::LABEL_BLACKLIST, self::LABEL_LOCKOUT, self::LABEL_MALICIOUS) as $lab){
			if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.$lab)) continue;
			
			$ipAddrs = explode("\n", file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.$lab));
			if(!count($ipAddrs)) continue;
			
			foreach($ipAddrs as $addr){
				$addr = trim($addr);
				if(!strlen($addr)) continue;
				
				if($addr == $remoteAddr) return true;

				//*印がある場合
				if(is_numeric(strpos($addr, ".*"))){
					$addr = substr($addr, 0, strpos($addr, ".*"));
					$res = strpos($remoteAddr, $addr);
					if(is_numeric($res) && $res === 0){
						http_response_code(403);
						echo "Access Denied.";
						exit;
					}
				}
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
	 * 二段階認証を許可しているか？
	 * @return bool
	 */
	public static function checkIsTwoFactorAuth(){
		return file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TWO_FACTOR);
	}

	/**
	 * @param bool
	 */
	public static function setTwoFactorAuthConfig(bool $on=true){
		if($on){
			touch(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TWO_FACTOR);
		}else{
			if(self::checkIsTwoFactorAuth()){
				unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_TWO_FACTOR);	
			}
		}
	}

	/**
	 * IPアドレスを直接入力してアクセスを禁止しているか？
	 * @return bool
	 */
	public static function checkIsRestrictIpDirectAccess(){
		return file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_IP_DIRECT_ACCESS);
	}

	/**
	 * @param bool
	 */
	public static function setRestrictIpDirectAccessConfig(bool $on=true){
		if($on){
			touch(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_IP_DIRECT_ACCESS);
		}else{
			if(self::checkIsRestrictIpDirectAccess()){
				unlink(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_IP_DIRECT_ACCESS);	
			}
		}
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

	/**
	 * @return bool
	 */
	public static function checkAllowUserAgent(){
		if(!file_exists(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_USER_AGENT)) return true;
		
		$agents = explode("\n", file_get_contents(ACCESS_RESTRICTIONS_CONFIG_DIR.self::LABEL_USER_AGENT));
		if(!count($agents)) return true;

		$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

		foreach($agents as $agent){
			$agent = trim($agent);
			if(!strlen($agent)) continue;

			if(is_numeric(strpos($userAgent, strtolower($agent)))){
				return false;
			}
		}
		
		return true;	
	}
}
