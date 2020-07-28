<?php
SOY2::import("domain.config.SOYShop_DataSets");
class UpdateDBLogic extends SOY2LogicBase{

	private $directory;
	private $extendDirectory;	//データベースの実行以外の更新
	private $checkVersionLogic;

	//DataSets (soycms_admin_data_sets)でのclass名
	const VERSION_KEY = "SOYSHOP_DB_VERSION";

	/*
	 * 更新ファイルの正規表現
	 * 例：update-1.5.sql
	 */
	const UPDATE_FILE_REGEX = "/^update-([.0-9]+)\\.sql\$/";

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		$this->db = new SOY2DAO();
		$this->checkVersionLogic = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic");
		$this->setDirectory();
	}

	/**
	 * データベースを更新する
	 */
	public function update() {

		$logic = $this->checkVersionLogic;

		//現在のデータベースのバージョンを取得する
		$current = $logic->getCurrentVersion();

		//updateのSQLファイルを取得する
		$sql_files = $logic->getUpdateFiles();

		//現在のデータベースのバージョンより上のupdateを実行する
		foreach($sql_files as $sql_file){
			//ファイル名からバージョンを取得するように変更→環境によってはupdate-n.sql以外のファイルが混じっていることがあるため
			preg_match('/update-(\d*).sql/', $sql_file, $tmp);
			if(!isset($tmp[1]) || !is_numeric($tmp[1])) continue;
			$version = (int)$tmp[1];

			if($version > $current && strpos($sql_file, ".sql") !== false){
				self::_executeSqlFile($sql_file, $version);
				$this->registerVersion($version);
			}
		}
	}

	/**
	 * 指定したファイルのSQL文を実行
	 */
	private function _executeSqlFile($file,$version=1){

		$sqls = self::getSqlQuery($file);
		foreach($sqls as $sql){
			if(strlen(trim($sql)) < 1) continue;
			try{
				$this->db->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				error_log(var_export($e, true));
				continue;
			}
		}

		//データベース以外の更新
		if(file_exists($this->extendDirectory . "extendUpdate-" . $version . ".php")){
			include_once($this->extendDirectory . "extendUpdate-" . $version . ".php");
		}
	}

	/**
	 * 指定したファイルからコメントを削除したSQL文を配列として取得する
	 * @return Array<string>
	 */
	private function getSqlQuery($file){

		$texts = array();

		$sqls = file_get_contents($this->directory . "/" . $file);
		if(strlen($sqls)){
			//コメント削除
			$sqls = preg_replace("/#.*\$/m", "", $sqls);

			//改行統一
			$sqls = strtr($sqls, array("\r\n" => "\n", "\r" => "\n"));

			$sqls = explode(";", $sqls);
			foreach($sqls as $sql){
				if(strlen(trim($sql)) < 1) continue;
				$texts[] = trim($sql) . ";";
			}
		}

		return $texts;
	}

	/**
	 * バージョン番号を保存する
	 * @param string version
	 */
	function registerVersion($version){
		try{
			SOYShop_DataSets::put(self::VERSION_KEY, $version);
		}catch(Exception $e){
			error_log(var_export($e, true));
		}
	}

	function setDirectory(){
		$this->directory = SOY2::RootDir() . "logic/upgrade/sql/" . SOYSHOP_DB_TYPE ."/";
		$this->extendDirectory = SOY2::RootDir() . "logic/upgrade/extend/";
	}


	/** ここから、includeするPHPファイル内で使用される関数 **/

	function copyDirectory($from, $to){

		$files = scandir($from);

		if($from[strlen($from)-1] != "/") $from .= "/";
		if($to[strlen($to)-1] != "/") $to .= "/";

		foreach($files as $file){
			if($file[0] == ".") continue;

			if(is_dir($from . $file)){
				if(!file_exists($to . $file)) mkdir($to . $file);
				$this->copyDirectory($from . $file, $to . $file);
				continue;
			}else{

				file_put_contents(
					$to . $file
					,file_get_contents($from . $file)
				);
			}
		}
	}

	function replaceTemplate($html){
		if(!defined("SOYSHOP_SITE_NAME")) define("SOYSHOP_SITE_NAME", "インテリアショップLBD");
		$url = parse_url(SOYSHOP_SITE_URL);
		$path = $url["path"];
		if($path[strlen($path) - 1] == "/") $path = substr($path, 0, strlen($path) - 1);
		$html = str_replace("@@SOYSHOP_URI@@", $path, $html);
		$html = str_replace("@@SOYSHOP_NAME@@", SOYSHOP_SITE_NAME, $html);

		return $html;
	}

	/**
	 * check for init mail config
	 * @param true = tmp_register, false = register
	 * @return bool
	 */
	function checkInitMail($bool = true){

		$type = ($bool) ? "tmp_register" : "register";

		$logic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$get = SOYShop_DataSets::get("mail.mypage.$type.header", null);

		return is_null($get);
	}


	/**
	 * init mail config
	 * @param true = tmp_register, false = register
	 */
	function initMailText($bool=true){
		$logic = SOY2Logic::createInstance("logic.mail.MailLogic");

		if($bool){
			$mail = array(
				"title" => "[#SHOP_NAME#]仮登録メール",
				"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/tmp_register/header.txt"),
				"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/tmp_register/footer.txt")
			);
			$logic->setMyPageMailConfig($mail, "tmp_register");

		}else{
			$mail = array(
	    		"title" => "[#SHOP_NAME#]登録完了メール",
	    		"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/register/header.txt"),
	    		"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/register/footer.txt")
	    	);
			$logic->setMyPageMailConfig($mail, "register");
		}
	}
}
