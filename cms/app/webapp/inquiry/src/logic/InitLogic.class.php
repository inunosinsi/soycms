<?php

class InitLogic extends SOY2LogicBase{

	private $initCheckFile;

	/**
	 * SOY Inquiryを初期化する
	 */
    public function init(){
    	self::initTable();
    }

    /**
     * テーブルを初期化する
     * @todo
     */
    private function initTable(){
    	$db = new SOY2DAO();
    	$db->begin();
    	$sqls = file_get_contents(dirname(__FILE__)."/table_". SOYINQUIRY_DB_MODE .".sql");
    	$sqls = explode(";",$sqls);
    	foreach($sqls as $sql){
    		if(strlen(trim($sql))<1) continue;
    		try{
    			$db->executeUpdateQuery($sql,array());
    		}catch(Exception $e){
				//
    		}
    	}

    	if(!file_exists($this->initCheckFile)){
    		file_put_contents($this->initCheckFile, "created:" . date("Y-m-d H:i:s"));
    	}

		sleep(1);

		if(file_exists($this->initCheckFile)){
			$db->commit();
		}

		//自動アップグレードをすべて実行しておく
		SOY2Logic::createInstance("logic.upgrade.UpdateDBLogic")->update();
    }

	function checkAndCreateTable(){
		$dao = new SOY2DAO();

		$sqls = file_get_contents(dirname(__FILE__)."/table_". SOYINQUIRY_DB_MODE .".sql");
    	$sqls = explode(";",$sqls);
		foreach($sqls as $sql){
			if(strlen(trim($sql))<1) continue;

			preg_match('/CREATE TABLE(.*?)\(/', $sql, $tmp);
			if(!isset($tmp[1])) continue;

			$tableName = trim($tmp[1]);
			try{
				$res = $dao->executeQuery("SELECT * FROM " . $tableName . " LIMIT 1;");
			}catch(Exception $e){
				try{
					$dao->executeUpdateQuery($sql, array());
				}catch(Exception $e){
					//
				}
			}
		}
	}

	/**
	 * initCheckFileのsetter
	 * SQLiteの場合は原則としてデータベースファイルそのものを指定する
	 */
	public function setInitCheckFile($file){
		$this->initCheckFile = $file;
	}
}
