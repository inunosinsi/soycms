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
    		$db->executeUpdateQuery($sql,array());
    	}
    	if(!file_exists(CMS_COMMON . "db/lpo.db"))file_put_contents(CMS_COMMON . "db/lpo.db", "created:" . date("Y-m-d H:i:s"));
    	
    	
    	//ディフォルト値を入れる
    	$sql = "INSERT INTO soylpo_list (id,title,mode,create_date,update_date,is_public) VALUES (1,'ディフォルト',0,".time().",".time().",1);";
    	try{
    		$db->executeQuery($sql);
    	}catch(Exception $e){
    	}
    	
    	//初期設定を入れる
    	$sql = "INSERT INTO soylpo_config (id,wisywig) VALUES (1,1);";
    	try{
    		$db->executeQuery($sql);
    	}catch(Exception $e){
    	}
    		
    }
    
}
?>