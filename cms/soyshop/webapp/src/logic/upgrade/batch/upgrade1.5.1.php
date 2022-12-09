<?php
/**
 * Upgrade file to SOY Shop 1.5.1
 * @change add new table for multi_category_config
 */
function execute(){

	//mypage auto login

	$db = new SOY2DAO();
	$exist = "select * from soyshop_categories;";

	try{
		$res = $db->executeQuery($exist);
		if($res){
			_echo("・テーブル追加はすでに行われています。");
			exit;
		}
	}catch(Exception $e){
		$res = true;
	}

	if($res){
		/* not exist auto login tabel */
		if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){

			$sql = sqlMySQL();
		}else{
			$sql = sqlSQLite();
		}

		$sql = explode(";",$sql);

		foreach($sql as $query){
			$query = trim($query);
			if(!$query)continue;

			try{
				$db->executeUpdateQuery($query);
			}catch(Exception $e){
				_echo("・テーブルの追加を失敗しました");
				exit;
			}
		}

		$link = SOY2PageController::createLink("");
		_echo("アップグレードバッチは終了しました。");
		_echo("商品の複数カテゴリ設定が追加されました。");
		_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
		exit;
	}

	_echo("・テーブル追加はすでに行われています。");
	exit;
}


function sqlSQLite(){
	$sql = <<<SQL

create table soyshop_categories(
	id integer primary key,
	item_id integer not null,
	category_id integer not null,
	attribute varchar
);

SQL;

	return $sql;
}

function sqlMySQL(){
	$sql = <<<SQL

create table soyshop_categories(
	id integer primary key auto_increment,
	item_id integer not null,
	category_id integer not null,
	attribute varchar(255)
) ENGINE=InnoDB default character set utf8;

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