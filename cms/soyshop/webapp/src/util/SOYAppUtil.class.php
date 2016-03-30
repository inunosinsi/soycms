<?php

class SOYAppUtil {

	public static function checkAppAuth($appId="inquiry"){
		
		$auth = false;
		$useSiteDb = false;

		if($appId == "inquiry"){
			$useSiteDb = (defined("SOYINQUIRY_USE_SITE_DB") && SOYINQUIRY_USE_SITE_DB);
		}else{
			$useSiteDb = (defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB);
		}
		
		if($useSiteDb){
			$session = SOY2ActionSession::getUserSession();
				
			//ルート権限の場合、サイト側のデータベースの定数がtrueだったら絶対にtrue
			if($session->getAttribute("isdefault"))	return true;
				
			$userId = $session->getAttribute("userid");
				
			$old = self::switchAdminMode();
				
			$appDao = SOY2DAOFactory::create("admin.AppRoleDAO");
			try{
				$appRoles = $appDao->getByUserId($userId);
			}catch(Exception $e){
				$appRoles = array();
			}
				
			self::resetAdminMode($old);
			
			//SOY Appで設定されている権限を調べる
			if(isset($appRoles[$appId]) && $appRoles[$appId]->getAppRole() > 0){
				$auth = true;
			}				
		}

		return $auth;	
	}

	public static function createAppLink(){
		//index.phpがある場合はindex.phpの二つ前のディレクトリまで戻る
		if(strpos($_SERVER["REQUEST_URI"], "index.php")!==false){
			$adminPath = substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "/index.php"));
		}else{
			$adminPath = $_SERVER["REQUEST_URI"];
		}
		return dirname($adminPath) . "/app/index.php";
	}
	
	public static function switchAdminDsn(){
		$old = self::switchAdminMode();
		return $old;
	}
	
	public static function resetAdminDsn($old){
		self::resetAdminMode($old);
	}
	
	private static function switchAdminMode(){
		$old = array();
		
		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();
		
		if(!defined("CMS_COMMON")){
			$common = str_replace("/soyshop/", "/common/", SOYSHOP_ROOT);
			define ("CMS_COMMON", $common);
		}
		$entity = CMS_COMMON . "domain/";
		
		SOY2::RootDir(CMS_COMMON);
		SOY2DAOConfig::DaoDir($entity);
		SOY2DAOConfig::EntityDir($entity);
		
		//MySQL版
		if(file_exists(dirname(SOYSHOP_ROOT) . "/common/config/db/mysql.php")){
			include_once(dirname(SOYSHOP_ROOT) . "/common/config/db/mysql.php");
			
		//SQLite版
		}else{
			include_once(dirname(SOYSHOP_ROOT) . "/common/config/db/sqlite.php");
		}
		
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);
		
		return $old;
	}
	
	private static function resetAdminMode($old){
		
		SOY2::RootDir($old["root"]);
		SOY2DAOConfig::DaoDir($old["dao"]);
		SOY2DAOConfig::EntityDir($old["entity"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);
	}
	
	public static function switchAppMode($appId){
		$old = array();
		
		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();
		
		$root = str_replace("/soyshop/", "/app/webapp/" . $appId . "/src/", SOYSHOP_ROOT);
		$entity = $root . "domain/";
		
		if(!defined("CMS_COMMON")){
			$common = str_replace("/soyshop/", "/common/", SOYSHOP_ROOT);
			define("CMS_COMMON", $common);
		}
		SOY2::RootDir(CMS_COMMON);	//管理DSNの取得のため
		
		//MySQL版
		if(file_exists(dirname(SOYSHOP_ROOT) . "/common/config/db/mysql.php")){
			include_once(dirname(SOYSHOP_ROOT) . "/common/config/db/mysql.php");
			
			$dsn = ADMIN_DB_DSN;
		//SQLite版
		}else{
			include_once(dirname(SOYSHOP_ROOT) . "/common/config/db/sqlite.php");
			
			switch($appId){
				case "mail":
					$dbName = "soymail";
					break;
				default:
					$dbName = $appId;
					break;
			}
			
			$dsn = str_replace("/cms.db", "/" . $appId . ".db", ADMIN_DB_DSN);
		}
		
		SOY2::RootDir($root);
		SOY2DAOConfig::DaoDir($entity);
		SOY2DAOConfig::EntityDir($entity);
		SOY2DAOConfig::Dsn($dsn);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);
		
		return $old;
	}
	
	//名前が紛らわしいので、名前だけ別のメソッドを用意。resetAdminModeと同じことを行う。
	public static function resetAppMode($old){
		self::resetAdminMode($old);
	}
}
?>