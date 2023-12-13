<?php
/**
 * トークンログイン(パスワード無しログイン)はデータベースのTokenLoginテーブルの有無で判断する
 */
class TokenLoginUtil {

	const ENDPOINT_URI_KEY = "token_login_endpoint_uri";
	const ENDPOINT_URI = "token_check";

	const ALLOW_TOKEN_LOGIN_PERIOD_KEY = "token_login_period";
	const ALLOW_TOKEN_LOGIN_PERIOD_DEFAULT = 7;

	/**
	 * @param string
	 * @return bool
	 */
	public static function login(string $token){
		if(!self::isTokenLoginMode()) return false;

		// ログインチェック
		$dao = SOY2DAOFactory::create("admin.TokenLoginDAO");
		$dao->prepare();
		try{
			$userId = (int)$dao->getByToken($token)->getUserId();
		}catch(Exception $e){
			$userId = 0;
		}
		if($userId <= 0) return false;

		try{
			$admin = SOY2DAOFactory::create("admin.AdministratorDAO")->getById($userId);
		}catch(Exception $e){
			$admin = new Administrator();
		}
		if(!is_numeric($admin->getId())) return false;
		
		// ログイン成功
		SOY2::import("util.UserInfoUtil");
		UserInfoUtil::login($admin, true);
		
		return true;
	}

	public static function turnOnTokenLoginMode(){
		if(self::isTokenLoginMode()) return;
		
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery(self::_schema());
		}catch(Exception $e){
			//var_dump($e);
		}
	}

	public static function turnOffTokenLoginMode(){
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("DROP TABLE TokenLogin;");
		}catch(Exception $e){
			//var_dump($e);
		}
	}

	/**
	 * データベースの有り無しで判断
	 * @return bool
	 */
	public static function isTokenLoginMode(){
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery("SELECT user_id FROM TokenLogin LIMIT 1;");
			return true;
		}catch(Exception $e){
			//
		}
		return false;
	}

	/**
	 * @param int
	 * @return bool
	 */
	public static function saveTokenLoginByUserId(int $userId){
		if($userId === 0 || !self::isTokenLoginMode()) return false;

		
		$lim = strtotime("+".self::getAllowTokenLoginPeriod()."day");
		$token = md5((string)$userId.(string)$lim);

		$dao = SOY2DAOFactory::create("admin.TokenLoginDAO");
		$obj = new TokenLogin();
		$obj->setUserId($userId);
		$obj->setToken($token);
		$obj->setLimit($lim);
		
		try{
			$dao->insert($obj);
			return true;
		}catch(Exception $e){
			//
		}
		return false;
	}

	/**
	 * @param int
	 * @return bool
	 */
	public static function updateTokenLoginByUserId(int $userId){
		if($userId === 0 || !self::isTokenLoginMode()) return false;

		$obj = self::_getTokenLoginObject($userId);
		$lim = strtotime("+".self::getAllowTokenLoginPeriod()."day");
		$obj->setLimit($lim);
		
		try{
			SOY2DAOFactory::create("admin.TokenLoginDAO")->update($obj);
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * @param int
	 * @return bool
	 */
	public static function removeTokenLoginByUserId(int $userId){
		if($userId === 0 || !self::isTokenLoginMode()) return false;

		$dao = SOY2DAOFactory::create("admin.TokenLoginDAO");
		$dao->prepare();

		try{
			$dao->deleteByUserId($userId);
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * TokenLoginユーザ
	 * @param int
	 * @return bool
	 */
	public static function isTokenLoginUser(int $userId){
		if(!self::isTokenLoginMode()) return false;
		return (is_numeric(self::_getTokenLoginObject($userId)->getUserId()));
	}

	/**
	 * @param string
	 * @return bool
	 */
	public static function createEndPoint(string $uri=self::ENDPOINT_URI){
		self::deleteEndPoint();
		if(!strlen($uri)) $uri = self::ENDPOINT_URI;

		SOY2::import("domain.admin.AdminDataSets");
		AdminDataSets::put(self::ENDPOINT_URI_KEY, $uri);
		
		$dir = $_SERVER["DOCUMENT_ROOT"]."/".$uri."/";
		if(!file_exists($dir)) mkdir($dir);		

		file_put_contents($dir."index.php", self::_buildEndpoint());
	}

	public static function deleteEndPoint(){
		SOY2::import("domain.admin.AdminDataSets");
		$uri = AdminDataSets::get(self::ENDPOINT_URI_KEY, self::ENDPOINT_URI);
		AdminDataSets::delete(self::ENDPOINT_URI_KEY);
		if(!is_string($uri)) $uri = self::ENDPOINT_URI;
		
		$dir = $_SERVER["DOCUMENT_ROOT"]."/".$uri."/";

		if(file_exists($dir."index.php")){
			unlink($dir."index.php");
			rmdir($dir);
		}
	}

	/**
	 * @param int
	 * @return string
	 */
	public static function buildEntpointUrlByUserId(int $userId){
		$u = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
		$u .= "://".$_SERVER["HTTP_HOST"]."/";
		$u .= self::getEndpointUri()."/index.php?token=";
		return $u.(string)self::_getTokenLoginObject($userId)->getToken();
	}

	/**
	 * @param int
	 * @return string
	 */
	public static function getLoginUrlExpiryByUserId(int $userId){
		return (int)self::_getTokenLoginObject($userId)->getLimit();
	}
	

	/**
	 * @return string
	 */
	public static function getEndpointUri(){
		SOY2::import("domain.admin.AdminDataSets");
		$u = AdminDataSets::get(self::ENDPOINT_URI_KEY, self::ENDPOINT_URI);
		if(!is_string($u) || !strlen(trim($u))) $u = self::ENDPOINT_URI;
		return $u;
	}

	/**
	 * @return int
	 */
	public static function getAllowTokenLoginPeriod(){
		SOY2::import("domain.admin.AdminDataSets");
		$d = AdminDataSets::get(self::ALLOW_TOKEN_LOGIN_PERIOD_KEY, self::ALLOW_TOKEN_LOGIN_PERIOD_DEFAULT);
		if(!is_numeric($d)) $d = self::ALLOW_TOKEN_LOGIN_PERIOD_DEFAULT;
		return $d;
	}

	/**
	 * @return string
	 */
	private static function _schema(){
		return file_get_contents(SOY2::RootDir()."sql/init_token_login_".SOYCMS_DB_TYPE.".sql");
	}

	/**
	 * @return string
	 */
	private static function _buildEndpoint(){
		$scriptName = trim($_SERVER["SCRIPT_NAME"],"/");
		$cmsDir = substr($scriptName, 0, strpos($scriptName, "/"));
		
		$script = file_get_contents(SOY2::RootDir()."config/token_login_script.php.sample");
		return str_replace("##CMS_DIRECTORY##", $cmsDir, $script);
	}

	/**
	 * @paran int
	 * @return 
	 */
	private static function _getTokenLoginObject(int $userId){
		static $objects;
		if(!is_array($objects)) $objects = array();
		if(!isset($objects[$userId])){
			$dao = SOY2DAOFactory::create("admin.TokenLoginDAO");
			$dao->prepare();
			try{
				$objects[$userId] = $dao->getByUserId($userId);
			}catch(Exception $e){
				$objects[$userId] = new TokenLogin();
			}
		}
		return $objects[$userId];
	}
}