<?php

class InitLogic extends SOY2LogicBase{

	function init() {
		$this->initTable();
	}
	
	/**
	 * テーブルを初期化する
	 * @todo
	 */
	private function initTable(){
		$db = new SOY2DAO();
		
		$sqls = file_get_contents(dirname(__FILE__)."/table_". SOYCMS_DB_TYPE .".sql");
		$sqls = explode(";",$sqls);
		
		foreach($sqls as $sql){
			if(strlen(trim($sql))<1)continue;
			try{
				$db->executeUpdateQuery($sql,array());
			}catch(Exception $e){
				var_dump($e);
			}
		}
		if(!file_exists(CMS_COMMON . "db/gallery.db")) file_put_contents(CMS_COMMON . "db/gallery.db", "created:" . date("Y-m-d H:i:s"));			
	}
	
}
?>