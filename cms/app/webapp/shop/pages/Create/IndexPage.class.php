<?php
class IndexPage extends SOYShopWebPage{

	var $form = array();
	private $disabled = false;

	function doPost(){

		if(isset($_POST["site_id"])){

			//フォームからデータの受取
			$siteId = trim($_POST["site_id"]);
			$siteName = $_POST["site_name"];
			$session = SOY2ActionSession::getUserSession();
			$session->setAttribute("soyshop.shop.id", $siteId);

			//サイトのチェック
			self::_checkSite($siteId, $siteName);

			//テンプレートID
			define("SOYSHOP_TEMPLATE_ID", "bryon");

			//サイト名
			define("SOYSHOP_SITE_NAME", $siteName);

			$dbtype = $_POST["dbtype"] ? "mysql" : "sqlite";

			$option = array();
			$option["dbtype"] = "sqlite";

			if($dbtype == "mysql"){

				$mysql = $_POST["MySQL"];
				foreach($mysql as $k => $v){
					$mysql[$k] = trim($v);
				}

				$dsn = "mysql:host=" . $mysql["host"] . ";dbname=" . $mysql["db"];
				if(isset($mysql["port"]) && strlen($mysql["port"])) $dsn .= ";port=" . $mysql["port"];
				$dsn .= ";charset=utf8";// PHP 5.3.6未満では無視される

				$option["dsn"] = $dsn;
				$option["user"] = $mysql["user"];
				$option["pass"] = $mysql["pass"];
				$option["dbtype"] = "mysql";

				//エラーチェック
				if(self::_checkMySQLSetting($mysql)){
					//接続テスト
					self::_checkDB($option);
				}
			}

			if(count($this->errors)) return;//エラーなければサイト作成

			//管理画面のみ使用
			$isOnlyAdmin = (isset($_POST["only_admin"]) && (int)$_POST["only_admin"] === 1);

			//soyshop init.phpのinclude
			include_once(dirname(CMS_COMMON) . "/soyshop/init.php");
			$res = init_soyshop($siteId, $option, $siteName, false, $isOnlyAdmin);	//実行

			if($res){
				// @ToDo 下記の処理はいずれは外したい
				// $path = soy2_realpath(SOYCMS_TARGET_DIRECTORY);
				// $path .= $siteId."/";
				//
				// $dsn = "";
				// if($option["dbtype"] == "mysql"){
				// 	$dsn = $option["dsn"];
				// }else{
				// 	$dsn = "sqlite:" . $path . ".db/sqlite.db";
				// }
				//
			   	// $obj = new SOYShop_Site();
		    	// $obj->setSiteId($siteId);
		    	// $obj->setName($siteName);
		    	// $obj->setPath(SOYSHOP_SITE_DIRECTORY);
		    	// $obj->setUrl(SOYSHOP_SITE_URL);
		    	// $obj->setDsn($dsn);//DSNだけ入っていてもあまり意味がないかも
				//
		    	// try{
		    	// 	$obj->save();
		    	// }catch(Exception $e){
				// 	$this->errors["init_failed"] = true;
		    	// }
				$this->jump("");
			}else{
				$this->errors["init_failed"] = true;
			}
		}
	}

	function __construct(){
		parent::__construct();

		self::_checkDirectory();

		self::_buildForm();
		self::_buildMySQLForm();
	}


	private function _buildForm(){

		$this->addForm("init_form", array(
			"disabled" => $this->disabled
		));

		$this->addInput("create_button", array(
			"disabled" => $this->disabled
		));

		$id = (isset($_POST["site_id"]))? $_POST["site_id"] : "shop";
		$this->addInput("site_id", array(
			"name" => "site_id",
			"value" => $id,
		));

		$name = (isset($_POST["site_name"]))? $_POST["site_name"] : "";
		$this->addInput("site_name", array(
			"name" => "site_name",
			"value" => $name,
		));

		//MySQL
		$selected = isset($_POST["dbtype"]) && $_POST["dbtype"];

		$this->addCheckBox("dbtype_mysql", array(
			"name" => "dbtype",
			"elementId" => "dbtype_mysql",
			"value" => 1,
			"selected" => $selected
		));

		//SQLite
		$this->addCheckBox("dbtype_sqlite", array(
			"name" => "dbtype",
			"elementId" => "dbtype_sqlite",
			"value" => 0,
			"selected" => !$selected
		));
	}

	/**
	 * build form for MySQL
	 */
	private function _buildMySQLForm(){

		$host = "";
		$port = "";
		$db = "";

		$dsn = (SOYCMS_DB_TYPE == "mysql") ? ADMIN_DB_DSN : "";
		$user = (SOYCMS_DB_TYPE == "mysql") ? ADMIN_DB_USER : "";
		$pass = (SOYCMS_DB_TYPE == "mysql") ? ADMIN_DB_PASS : "";

		if(strlen($dsn)>0){
			preg_match('/host=(.*?)(?:;|$)/', $dsn, $match);
			$host = $match[1];
			preg_match('/port=(.*?)(?:;|$)/', $dsn, $match);
			$port = (isset($match[1])) ? $match[1] : 3306;	//portは省略可の為
			preg_match('/dbname=(.*?)(?:;|$)/', $dsn, $match);
			$db = $match[1];
		}

		if(isset($_POST["MySQL"])){
			$host = $_POST["MySQL"]["host"];
			$port = $_POST["MySQL"]["port"];
			$db   = $_POST["MySQL"]["db"];
			$user = $_POST["MySQL"]["user"];
			$pass = $_POST["MySQL"]["pass"];
		}

		$this->addInput("mysql_host", array(
			"name" => "MySQL[host]",
			"value" => $host
		));

		$this->addInput("mysql_port", array(
			"name" => "MySQL[port]",
			"value" => $port
		));

		$this->addInput("mysql_db", array(
			"name" => "MySQL[db]",
			"value" => $db
		));

		$this->addInput("mysql_user", array(
			"name" => "MySQL[user]",
			"value" => $user
		));

		$this->addInput("mysql_pass", array(
			"name" => "MySQL[pass]",
			"value" => $pass
		));

		$this->addCheckBox("only_admin", array(
			"name" => "only_admin",
			"value" => 1,
			"label" => "管理画面のみ使用する"
		));
	}

	/**
	 * ディレクトリの書き込み権限チェック
	 */
	private function _checkDirectory(){
		$this->errors["error_soycms_site_dir"] = ( !file_exists(SOYCMS_TARGET_DIRECTORY) || !is_dir(SOYCMS_TARGET_DIRECTORY) || !is_writable(SOYCMS_TARGET_DIRECTORY) );

		$soyshopConfDir = dirname(CMS_COMMON) . "/soyshop/webapp/conf/shop";
		$this->errors["error_soyshop_shop_conf_dir"] = ( !file_exists($soyshopConfDir) || !is_dir($soyshopConfDir) || !is_writable($soyshopConfDir) );

		//フォームの有効無効
		$this->disabled = $this->errors["error_soycms_site_dir"] || $this->errors["error_soyshop_shop_conf_dir"];

		//エラー文言中のパスなど
		$this->addLabel("soycms_site_dir", array(
			"text" => SOYCMS_TARGET_DIRECTORY
		));
		$this->addLabel("soyshop_conf_dir", array(
			"text" => $soyshopConfDir
		));
		$this->addLabel("run_user", array(
			"text" => defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE ? fileowner($_SERVER["SCRIPT_FILENAME"]) : "Apacheの実行ユーザー"
		));
	}


	/**
	 * @param siteId
	 * @param siteName
	 * @return Boolean
	 */
	private function _checkSite($siteId, $siteName){
		$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");

		//site id empty
		if(strlen(trim($siteId)) == 0){
			$this->errors["site_id_empty"] = true;
		}

		//site id unique
		$site = ShopUtil::getSiteBySiteId($siteId);
		if(is_numeric($site->getId())){
			$this->errors["site_id_unique"] = true;
		}

		//site id format
		if(!preg_match('/^[\-_a-zA-Z0-9]+$/', $siteId)){
			$this->errors["site_id_format"] = true;
		}

		if(!preg_match('/^[a-zA-Z]/', $siteId)){
			$this->errors["site_id_format_init"] = true;
		}

		//site name empty
		if(strlen(trim($siteName)) == 0){
			$this->errors["site_name_empty"] = true;
		}
	}

	private function _checkMySQLSetting($input){
		$noError = true;
		if(strlen($input["host"]) == 0){
			$this->errors["mysql_host_empty"] = true;
			$noError = false;
		}
		if(strlen($input["db"]) == 0){
			$this->errors["mysql_db_empty"] = true;
			$noError = false;
		}
		return $noError;
	}

	/**
	 * @param db datebase sessting
	 * @return Boolean
	 */
	private function _checkDB($db){

		try{
			SOY2DAOConfig::setOption("connection_failure","throw");
			$pdo = SOY2DAO::_getDataSource($db["dsn"], $db["user"], $db["pass"]);

			try{
				$value = $pdo->exec("select * from soyshop_item");

				//Exceptionではなくfalseを返す時対策
				//テーブルがあれば通常0が返る（データの数ではない）
				if($value !== false){
					$this->errors["db_exist_table"] = true;
					$res = false;
				}else{
					$res = true;
				}
			}catch(Exception $e){
				//ok: dbに接続できるがテーブルがない状態
				$res = true;
			}

		}catch(Exception $e){
			$this->errors["db_connect"] = true;
			$res = false;
		}

		return $res;
	}
}
