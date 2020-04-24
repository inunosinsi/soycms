<?php
function execute(){	
	set_time_limit(0);
	$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
	}else{
		$sqlAdd = sqlSQLiteAdd();
	}
	
	_echob("<br />[アイテム(カラム追加)]");
	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$itemDao->executeUpdateQuery($query);
			$flg = true;
		}catch(Exception $e){
			
		}
	}
	
	if($flg){
		_echo("・アイテムの削除フラグカラム追加を行いました。");
	}else{
		_echo("・アイテムの削除フラグカラム追加は失敗しました。");
	}	
	
	
	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;

}


function sqlSQLiteAdd(){
	$sql = <<<SQL
ALTER TABLE soyshop_item ADD COLUMN is_disabled INTEGER default 0;

SQL;

	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_item ADD COLUMN is_disabled INTEGER default 0;

SQL;

	return $sql;
}


function _echo($str=""){
	echo $str."<br />";
}

function _echob($str=""){
	_echo("<b>" . $str."</b>");
}
?>