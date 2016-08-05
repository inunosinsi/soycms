<?php

class UpgradeLogic extends SOY2LogicBase{

    function execute() {
    	
    	$dao = new SOY2DAO();
    			
		$sqls = array();
		$messages = array();
		
		//0.6.0の追加分
		list($sql,$message) = $this->getX6SQL();
		$sqls[] = $sql;
		$messages[] = $message;
		
		$mes = array();
		foreach($sqls as $key => $sql){
			try{
				$dao->executeQuery($sql,array());
				$mes[] = $messages[$key];
			}catch(Exception $e){
			}
		}
		
		echo "<h2>SOYListのバージョンアップ</h2>";
		if(count($mes)){
			echo implode("<br />",$mes);
		}else{
			echo "データベースの変更はありません<br />";
		}
		echo "<br />";
		$link = SOY2PageController::createLink(APPLICATION_ID);
		
		echo "<a href=\"".$link."\">管理画面へ</a>";
		exit;	
    }
    
    //0.6.0の追加分
    function getX6SQL(){
    	if(SOYCMS_DB_TYPE=="mysql"){
			$sql = <<<SQL
CREATE TABLE soylist_config (
	config TEXT
)ENGINE=InnoDB;
SQL;
		}else{
			$sql = <<<SQL
CREATE TABLE soylist_config (
	config TEXT
);
SQL;
		}
		$message = "configテーブルを追加しました";
    	
    	return array($sql,$message);
    }
}
?>