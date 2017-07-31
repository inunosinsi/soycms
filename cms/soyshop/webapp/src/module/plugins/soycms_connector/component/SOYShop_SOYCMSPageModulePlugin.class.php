<?php

class SOYShop_SOYCMSPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "cms";

	public static function configure($array = null){
		static $_config = array();
		if($array){
			$_config = $array;
		}

		return $_config;
	}

	public static function prepare($isFirst = false){

    $config = self::configure();

		$siteId = $config["siteId"];


		$old_dao_dir = SOY2DAOConfig::DaoDir();
		$old_entity_dir = SOY2DAOConfig::EntityDir();
		$old_dsn = SOY2DAOConfig::Dsn();
		$old_user = SOY2DAOConfig::user();
		$old_pass = SOY2DAOConfig::pass();

		SOY2DAOConfig::DaoDir($config["rootDir"] . "domain/");
		SOY2DAOConfig::EntityDir($config["rootDir"] . "domain/");

		//rootdir
		$config["old_rootdir"] = SOY2::RootDir();
		SOY2::RootDir($config["rootDir"]);

    $dbFilePath = SOY2::RootDir() . "config/db/mysql.php";

    //MySQL版
		if(file_exists($dbFilePath)){
			include_once($dbFilePath);
			$user = (defined("ADMIN_DB_USER"))? ADMIN_DB_USER : null;
			$pass = (defined("ADMIN_DB_PASS"))? ADMIN_DB_PASS : null;

		//SQLite版
		}else{
			include_once(SOY2::RootDir() . "config/db/sqlite.php");
			$user = null;
			$pass = null;
		}

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user($user);
		SOY2DAOConfig::pass($pass);

		try{
      $site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
    }catch(Exception $e){
      $site = new Site();
    }

		SOY2DAOConfig::Dsn($site->getDataSourceName());


		$config["db_old"] = array(
			"dao_dir" => $old_dao_dir,
			"entity_dir" => $old_entity_dir,
			"dsn" => $old_dsn,
			"user" => $old_user,
			"pass" => $old_pass
		);

		//rootdir
		$config["old_rootdir"] = SOY2::RootDir();
		SOY2::RootDir($config["rootDir"]);

		if(!defined("_SITE_ROOT_")) define("_SITE_ROOT_", $site->getPath());

		//必須クラスはここで読み込む
		if($isFirst){
			SOY2::import("util.CMSUtil");
			SOY2::import("site_include.CMSLabel");
			SOY2::import("site_include.CMSPageLinkPlugin");
			SOY2::import("util.UserInfoUtil");
		}

		self::configure($config);
	}

	public static function tearDown(){

		$config = self::configure();
		$db = $config["db_old"];

		SOY2DAOConfig::DaoDir($db["dao_dir"]);
		SOY2DAOConfig::EntityDir($db["entity_dir"]);
		SOY2DAOConfig::Dsn($db["dsn"]);
		SOY2DAOConfig::user($db["user"]);
		SOY2DAOConfig::pass($db["pass"]);

		//rootdir
		SOY2::RootDir($config["old_rootdir"]);

	}

	function execute(){
		$soyValue = $this->soyValue;

		$array = explode(".",$soyValue);
		if(count($array)>1){
			unset($array[0]);
		}
		$func = "soycms_" . implode("_", $array);

		$modulePath = soy2_realpath(_SITE_ROOT_) . ".module/" . str_replace(".", "/", $soyValue) . ".php";

		$this->setInnerHTML('<?php SOYShop_SOYCMSPageModulePlugin::prepare(); ob_start(); ' .
						'if(file_exists("'.$modulePath.'")){include_once("'.$modulePath.'");}else{@SOY2::import("site_include.module.'.$soyValue.'",".php");} ?>'.
						$this->getInnerHTML().'' .
						'<?php $tmp_html=ob_get_contents();ob_end_clean(); '.
						'if(function_exists("'.$func.'")){echo call_user_func("'.$func.'",$tmp_html,$this);}else{ echo "function not found : '.$func.'";} SOYShop_SOYCMSPageModulePlugin::tearDown(); ?>');
	}
}
