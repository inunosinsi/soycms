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
		
		echo "<h2>SOYCalenderのバージョンアップ</h2>";
		if(count($mes)){
			echo explode("<br />",$mes);
		}else{
			echo "データベースの変更はありません<br />";
		}
		echo "<br />";
		$link = SOY2PageController::createLink(APPLICATION_ID);
		
		echo "<a href=\"".$link."\">管理画面へ</a>";
		exit;
    }
    
    function getX6SQL(){
    	if(SOYCMS_DB_TYPE=="mysql"){
			$sql = "alter table soycalendar_title add attribute VARCHAR(255) after title";
		}else{
			$sql = "alter table soycalendar_title add attribute VARCHAR";
		}
		$message = "タイトルテーブルに属性カラムを追加しました";
		
		array($sql,$message);
    }
}
?>