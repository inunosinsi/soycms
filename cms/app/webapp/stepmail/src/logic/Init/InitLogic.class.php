<?php

class InitLogic extends SOY2LogicBase{

	private $dao;

	function check(){
		self::prepare();
		try{
			$this->dao->executeQuery("SELECT * FROM stepmail_mail LIMIT 1");
		}catch(Exception $e){
			return true;
		}
		
		return false;
	}

	function init() {
		$this->initTable();
	}
	
		/**
	 * テーブルを初期化する
	 * @todo
	 */
	private function initTable(){
		self::prepare();
		
		$sqls = file_get_contents(dirname(__FILE__) . "/sql/table_" . SOYCMS_DB_TYPE . ".sql");
		$sqls = explode(";", $sqls);
		foreach($sqls as $sql){
			if(strlen(trim($sql)) < 1) continue;
			try{
				$this->dao->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				//
			}
		}
	}
	
	private function prepare(){
		$this->dao = new SOY2DAO();
	}
}
?>