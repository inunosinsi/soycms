<?php

class ConvertImageUrlConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			//SQLite版の場合はバックアップをとる
			if(SOYCMS_DB_TYPE == "sqlite"){
				$backupDir = UserInfoUtil::getSiteDirectory() . ".backup/";
				if(!file_exists($backupDir)) mkdir($backupDir);
				$backupDir = $backupDir . date("Ymd") . "/";
				if(!file_exists($backupDir)) mkdir($backupDir);

				$dbFilePath = UserInfoUtil::getSiteDirectory() . ".db/sqlite.db";
				copy($dbFilePath, $backupDir . "sqlite.db");
			}

			$dao = new SOY2DAO();

			$domain = (strlen($_POST["domain"])) ? htmlspecialchars($_POST["domain"], ENT_QUOTES, "UTF-8") : $_SERVER["HTTP_HOST"];
			foreach(array("content", "more") as $col){
				$sql = "UPDATE Entry SET " . $col	. "=REPLACE(" . $col . ", 'src=\"http://" . $domain . "', 'src=\"');";
				try{
					$dao->executeUpdateQuery($sql);
				}catch(Exception $e){
					//REPLACE対応していない問題 まだ試していない テスト不十分のためコメントアウト
					//$length = strlen("http://" . $_POST["domain"]);
					// try{
					// 	//$sql = "UPDATE Entry SET " . $col	. " = SUBSTR(" . $col . ", " . $length . ") WHERE " . $col . " like 'http://" . $_POST["domain"] . "%';";
					// }catch(Exception $e){
					// 	var_dump($e);
					// }
					var_dump($e);
				}
			}

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("mode_sqlite", (SOYCMS_DB_TYPE == "sqlite"));
		DisplayPlugin::toggle("mode_mysql", (SOYCMS_DB_TYPE == "mysql"));

		$this->addForm("form");

		$this->addInput("domain", array(
			"name" => "domain",
			"value" => $_SERVER["HTTP_HOST"]
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
