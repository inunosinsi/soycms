<?php
/**
 * Upgrade file to SOY Shop 1.10.0
 */
function execute(){
	
	set_time_limit(0);
	$categoryAttributeDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
	
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
	}else{
		$sqlAdd = sqlSQLiteAdd();
	}

	_echob("<br />[カテゴリーカスタムフィールド(カラム追加)]");
	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$categoryAttributeDao->executeUpdateQuery($query);
			$flg = true;
		}catch(Exception $e){
			var_dump($e);
		}
	}
	
	if($flg){
		_echo("・カテゴリーカスタムフィールドの値2カラム追加を行いました。");
	}else{
		_echo("・カテゴリーカスタムフィールドの値2カラム追加は失敗しました。");
	}
	
	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
//	_echo("続いてファイルの上書きを実行してください。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;
}


function sql(){
	$sql = <<<SQL
	
SQL;

	return $sql;
}

function sqlSQLiteAdd(){
	$sql = <<<SQL
ALTER TABLE soyshop_category_attribute ADD COLUMN category_value2 varchar;
SQL;

	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL
ALTER TABLE soyshop_category_attribute ADD COLUMN category_value2 TEXT;
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