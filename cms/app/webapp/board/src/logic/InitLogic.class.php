<?php

class InitLogic extends SOY2LogicBase{

	/**
	 * SOY Boardを初期化する
	 */
    public function init(){
    	$this->initTable();
    }
    
    /**
     * テーブルを初期化する
     */
    private function initTable(){
    	$db = new SOY2DAO();
    	$db->begin();
    	
    	$sqls = file_get_contents(dirname(__FILE__)."/table_". SOYCMS_DB_TYPE .".sql");
    	$sqls = explode(";",$sqls);
		
		foreach($sqls as $sql){
    		if(strlen(trim($sql))<1)continue;
    		
    		try{
				$db->executeUpdateQuery($sql,array());
    		}catch(Exception $e){
    			
    		}
    	}
		
    	$dbfile = CMS_COMMON . "db/".APPLICATION_ID.".db";
						    	    		
    	if(SOYCMS_DB_TYPE == "mysql"){
    		if( !file_exists($dbfile)){
    			file_put_contents($dbfile, "created:" . date("Y-m-d H:i:s"));
    			$db->commit();
    		}
    	} elseif(SOYCMS_DB_TYPE == "sqlite"){
    			$db->commit();
    	}
    	
    }
    
}
?>