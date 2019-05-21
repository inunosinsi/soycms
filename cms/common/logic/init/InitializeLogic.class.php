<?php
class InitializeLogic implements SOY2LogicInterface{

	public static function getInstance($a,$b){
		return SOY2LogicBase::getInstance($a,$b);
	}

	/**
	 * 初期化を行います。
	 */
	function initialize($userId,$password){

		//すでにDBファイルが存在するなら何もしない
		if(ADMIN_DB_EXISTS == true){
			return false;
		}

		try{
			$this->initDB();

			$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
			$logic->createAdministrator($userId,$password,true);

			return true;

		}catch(Exception $e){
			//return false;
			//例外を投げてしまう
			throw($e);
		}
	}

	function initDB(){

		try{
			$pdo = new SOY2DAO();
			$sql = file_get_contents(CMS_SQL_DIRECTORY . "init_cms_".SOYCMS_DB_TYPE.".sql");
			$sqls = explode(";",$sql);

			$exception = null;
			foreach($sqls as $sql){
				$sql = trim($sql);
				try{
					if(empty($sql))continue;
					$pdo->executeUpdateQuery($sql,array());
				}catch(Exception $e){
					$exception = $e;
				}
			}

			if($exception)throw $e;

			//DBのバージョンを入れておく
			$this->registerDbVersion();
			$this->registerAdminVersion();

		}catch(Exception $e){
			//return false;
			//例外を投げてしまう
			throw $e;
		}

		$pdo = null;
		//MySQL版でも動作するようにファイルを作成します。
		if(SOYCMS_DB_TYPE == "mysql" && !file_exists(SOY2::RootDir() . "/db/cms.db")){
			file_put_contents(SOY2::RootDir() . "/db/cms.db","generated");
		}
	}

	/**
	 * データベース（admin）のバージョンを保存する
	 */
	function registerDbVersion(){
		$logic = SOY2LogicContainer::get("logic.db.DBVersionLogic", array(
			"target" => "admin"
		));
		$logic->registerCurrentSQLVersion();
	}

	/**
	 * データベース（admin）のバージョンを保存する
	 */
	function registerAdminVersion(){
		$logic = SOY2LogicContainer::get("logic.admin.Upgrade.AdminVersionLogic");
		$logic->registerCurrentScriptVersion();
	}

}
