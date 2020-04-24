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

ALTER TABLE soyshop_user ADD COLUMN point integer;

SQL;

	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_user ADD COLUMN point integer;

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