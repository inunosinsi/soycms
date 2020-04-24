<?php
function execute(){	
	
	$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
	}else{
		$sqlAdd = sqlSQLiteAdd();
	}
	
	_echob("<br />[ユーザー(カラム追加)]");
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
		_echo("・カラム追加は失敗しました。");
	}else{
		_echo("・カラム追加を行いました。");
	}

	/**
	 * @ToDo ニックネームとurlのデータの入れ替え
	 */
	$userFlag = true;
	try{
		$users = $dao->get();
	}catch(Exception $e){
		$userFlag = false;
	}
	
	if($userFlag){
		foreach($users as $user){
			$array = $user->getAttributesArray();
			$nickname = (isset($array["nickname"])&&strlen($array["nickname"]) > 0) ? $array["nickname"] : "";
			$url = (isset($array["url"])&&strlen($array["url"]) > 0) ? $array["url"] : "";
			
			//どちらの値もない場合
			if(strlen($nickname)==0&&strlen($url) == 0){
				//何もしない
			}else{
				$user->setNickname($nickname);
				$user->setUrl($url);
			}		
			
			$user->setAttributes(null);
			try{
				$dao->update($user);
			}catch(Exception $e){
				$flg = true;
			}
		}
	}
	
	$albumFlg = false;
	
	//テーブルの追加
	$dao = new SOY2DAO();
	
	//albumテーブル
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$albumSql = getMySQLAlbumTable();
	}else{
		$albumSql = getSQLiteAlbumTable();
	}
	
	try{
		$dao->executeUpdateQuery($albumSql);
	}catch(Exception $e){
		$albumFlg = true;
	}
	
	if($albumFlg==true){
		_echo("・アルバムテーブル追加は失敗しました。");
	}else{
		_echo("・アルバムテーブル追加を行いました。");
	}
	
	$photoFlg = false;
	
	//photoテーブル
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$photoSql = getMySQLPhotoTable();
	}else{
		$photoSql = getSQLitePhotoTable();
	}
	
	try{
		$dao->executeUpdateQuery($photoSql);
	}catch(Exception $e){
		$photoFlg = true;
	}
	
	if($photoFlg==true){
		_echo("・写真テーブル追加は失敗しました。");
	}else{
		_echo("・写真テーブル追加を行いました。");
	}
	
	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
//	_echo("続いてファイルの上書きを実行してください。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;

}


function sqlSQLiteAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_user ADD COLUMN nickname VARCHAR;
ALTER TABLE soyshop_user ADD COLUMN url VARCHAR;
ALTER TABLE soyshop_user ADD COLUMN image_path VARCHAR;

SQL;

	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_user ADD COLUMN nickname VARCHAR(255) AFTER reading;
ALTER TABLE soyshop_user ADD COLUMN url VARCHAR(255) AFTER fax_number;
ALTER TABLE soyshop_user ADD COLUMN image_path VARCHAR(255) AFTER nickname;

SQL;

	return $sql;
}

function getSQLiteAlbumTable(){
	$sql = <<<SQL
	
create table soyshop_album(
	id integer primary key,
	user_id integer not null,
	name varchar,
	description text,
	is_publish integer not null,
	create_date integer not null,
	update_date integer not null
);

SQL;

	return $sql;	
}

function getMySQLAlbumTable(){
	$sql = <<<SQL
	
create table soyshop_album(
	id integer primary key AUTO_INCREMENT,
	user_id integer not null,
	name varchar(255),
	description text,
	is_publish integer not null default 0,
	create_date integer not null,
	update_date integer not null
) ENGINE=InnoDB;

SQL;

	return $sql;	
}

function getSQLitePhotoTable(){
	$sql = <<<SQL
	
create table soyshop_photo(
	id integer primary key,
	user_id integer not null,
	album_id integer not null,
	image_path varchar not null,
	description text,
	is_publish integer not null default 0,
	create_date integer not null,
	update_date integer not null
);

SQL;

	return $sql;	
}

function getMySQLPhotoTable(){
	$sql = <<<SQL
	
create table soyshop_photo(
	id integer primary key AUTO_INCREMENT,
	user_id integer not null,
	album_id integer not null,
	image_path varchar(255) not null,
	description text,
	is_publish integer not null default 0,
	create_date integer not null,
	update_date integer not null
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