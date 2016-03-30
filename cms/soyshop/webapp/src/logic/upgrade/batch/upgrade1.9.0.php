<?php
/**
 * Upgrade file to SOY Shop 1.9.0
 */
function execute(){

	$db = new SOY2DAO();
	$exist = "select * from soyshop_mail_log;";

	try{
		$res = $db->executeQuery($exist);
		$exeFlag = false;
	}catch(Exception $e){
		$exeFlag = true;
	}
	
	//実行したかのフラグ
	$flag = false;
	
	if($exeFlag===true){
		/* not exist auto login tabel */
		if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
			$sql = sqlMySQL();
		}else{
			$sql = sqlSQLite();
		}
	
		$sql = trim($sql);
	
		try{
			$db->executeUpdateQuery($sql);
			$flag = true;
		}catch(Exception $e){
		}	
	}
	
	if($flag === true){
		_echo("・メールログテーブルを追加しました。");
	}else{
		_echo("・メールログテーブルを失敗しました。");
	}
	
	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
//	_echo("続いてファイルの上書きを実行してください。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;
}


function sqlSQLite(){
	$sql = <<<SQL

create table soyshop_mail_log(
	id integer primary key,
	recipient text,
	order_id integer,
	user_id integer,
	title text,
	content text,
	is_success tinyint not null default 0,
	send_date integer NOT NULL
);

SQL;

	return $sql;
}

function sqlMySQL(){
	$sql = <<<SQL

create table soyshop_mail_log(
	id integer primary key AUTO_INCREMENT,
	recipient text,
	order_id integer,
	user_id integer,
	title text,
	content text,
	is_success tinyint not null default 0,
	send_date integer NOT NULL
) ENGINE=InnoDB;

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