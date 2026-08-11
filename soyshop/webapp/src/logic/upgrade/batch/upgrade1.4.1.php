<?php

function execute(){


	_echo("<b>SOY Shop 1.4.1 Upgrade</b>");
	_echo("Run : ".date("Y/m/d H:i:s")."<br />");

	/* mail text user register */
	_echob("[マイページ 仮登録メールの設定]");
	if(checkInitMail(true)){
		//not yet init config
		initMailText(true);
		_echo("・仮登録メールの設定を行いました。");
	}else{
		//already initi
		_echo("・仮登録メールの設定はされています。");
	}

	_echob("<br />[マイページ 登録メールの設定]");
	if(checkInitMail(false)){
		//not yet init config
		initMailText(false);
		_echo("・登録メールの設定を行いました。");
	}else{
		//already initi
		_echo("・登録メールの設定はされています。");
	}




	/* real_register_date */

	$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
		$sqlCreate = sqlMySQLCreate();
	}else{
		$sqlAdd = sqlSQLiteAdd();
		$sqlCreate = sqlSQLiteCreate();
	}

	_echob("<br />[マイページ ユーザ登録(カラム追加)]");
	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$dao->executeUpdateQuery($query);

		}catch(Exception $e){
			$flg = true;
		}
	}

	if($flg){
		_echo("・カラム追加は行われています。");
	}else{
		_echo("・カラム追加を行いました。");
	}

	_echob("<br />[マイページ ユーザ登録(テーブル追加)]");
	$sqlCreate = explode(";",$sqlCreate);
	$flg = false;
	foreach($sqlCreate as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$dao->executeQuery($query);

		}catch(Exception $e){
			$flg = true;
		}
	}

	if($flg){
		_echo("・テーブル追加は行われています。");
	}else{
		_echo("・テーブル追加を行いました。");
	}


	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
	_echo("続いてファイルの上書きを実行してください。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;

}

/**
 * Column Add SQL for SQLite
 */
function sqlSQLiteAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_user ADD COLUMN user_type integer default 1;
ALTER TABLE soyshop_user ADD COLUMN real_register_date integer;

SQL;

	return $sql;
}

/**
 * Create Table SQL for SQLite
 */
function sqlSQLiteCreate(){
	$sql = <<<SQL

create table soyshop_user_token(
	id integer primary key,
	user_id integer not null,
	token varchar(255) not null,
	time_limit integer not null
);

SQL;

	return $sql;
}

/**
 * Column Add SQL for MySQL
 */
function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_user ADD COLUMN user_type integer default 1;
ALTER TABLE soyshop_user ADD COLUMN real_register_date integer;

SQL;

	return $sql;
}


/**
 * Create Table SQL for MySQL
 */
function sqlMySQLCreate(){
	$sql = <<<SQL

create table soyshop_user_token(
	id integer primary key auto_increment,
	user_id integer not null,
	token varchar(255) not null,
	time_limit integer not null
) ENGINE=INNODB default character set utf8;

SQL;

	return $sql;
}





/**
 * check for init mail config
 * @param true = tmp_register, false = register
 * @return bool
 */
function checkInitMail($bool=true){

	$type = ($bool)? "tmp_register" : "register";

	$logic = SOY2Logic::createInstance("logic.mail.MailLogic");
	$get = SOYShop_DataSets::get("mail.mypage.$type.header", null);

	return is_null($get);
}


/**
 * init mail config
 * @param true = tmp_register, false = register
 */
function initMailText($bool=true){

	$logic = SOY2Logic::createInstance("logic.mail.MailLogic");

		if($bool){
			$mail = array(
				"title" => "[#SHOP_NAME#]仮登録メール",
				"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/tmp_register/header.txt"),
				"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/tmp_register/footer.txt")
			);
			$logic->setMyPageMailConfig($mail,"tmp_register");

		}else{
			$mail = array(
	    		"title" => "[#SHOP_NAME#]登録完了メール",
	    		"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/register/header.txt"),
	    		"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/register/footer.txt")
	    	);
			$logic->setMyPageMailConfig($mail,"register");
		}


}

function _echo($str=""){
	echo $str."<br />";
}

function _echob($str=""){
	_echo("<b>" . $str."</b>");
}
?>