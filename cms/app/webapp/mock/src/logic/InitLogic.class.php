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
    	
    	$sqls = file_get_contents(dirname(__FILE__) . "/table_" . SOYCMS_DB_TYPE . ".sql");
    	$sqls = explode(";", $sqls);
    	foreach($sqls as $sql){
    		if(strlen(trim($sql)) < 1) continue;
    		$db->executeUpdateQuery($sql, array());
    	}
    	if(!file_exists(CMS_COMMON . "db/" . APPLICATION_ID . ".db")){
    		file_put_contents(CMS_COMMON . "db/" . APPLICATION_ID . ".db", "created:" . date("Y-m-d H:i:s"));
    	}
    }
}
?>