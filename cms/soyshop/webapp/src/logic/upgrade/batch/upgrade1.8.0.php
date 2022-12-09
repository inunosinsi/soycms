<?php
function execute(){	
	set_time_limit(0);
	$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
	}else{
		$sqlAdd = sqlSQLiteAdd();
	}
	
	_echob("<br />[ユーザ(カラム追加)]");
	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$userDao->executeUpdateQuery($query, array());
			$flg = true;
		}catch(Exception $e){
			
		}
	}
	
	if($flg){
		_echo("・ユーザのメールエラーカウントカラム追加を行いました。");
		_echo("アップグレードバッチは終了し、SOY Mailと連携出来る様になりました。");
	}else{
		_echo("・ユーザのメールエラーカウントカラム追加は失敗しました。");
	}	
	
	
	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;

}


function sqlSQLiteAdd(){
	$sql = <<<SQL
ALTER TABLE soyshop_user ADD COLUMN mail_error_count INTEGER default 0;
ALTER TABLE soyshop_user ADD COLUMN not_send INTEGER default 0;

SQL;

	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_user ADD COLUMN mail_error_count INTEGER default 0 AFTER memo;
ALTER TABLE soyshop_user ADD COLUMN not_send TINYINT default 0 AFTER mail_error_count;
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