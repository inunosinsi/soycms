<?php
//CartLogicの内容の一部をSQLite DBに移すことでPHP側のメモリを節約する →
//php.iniのメモリの配分をしないで運営できることを目標とする

/**
 * カートの中身を管理するためのデータベースを作成する
 * @return string db_file_name
 */
function soyshop_cart_init_db(){
	//古いデータベースはすべて削除
	soyshop_cart_routine_delete_db();

	$dbName = md5(time()) . rand(1, 100);
	$path = soyshop_cart_db_directory() . $dbName . ".db";
	if(!file_exists($path)){
		touch($path);
		$pdo = soyshop_cart_read_db($dbName);
		$pdo->query(soyshop_cart_build_table_sql());

		return $dbName;
	}
	return null;
}

/**
 * @return object PDO
 */
function soyshop_cart_read_db($dbName){
	$path = soyshop_cart_db_directory() . $dbName . ".db";
	if(!file_exists($path)) return null;

	//PDO
	try{
		return new PDO("sqlite:" . $path, "", "");
	}catch(Exception $e){
		/**
		 * @ToDo エラーの時はどうしよう？
		 */
		return null;
	}
}

//カートに入っている商品を取得
function soyshop_cart_get_items($dbName=null){
	if(is_null($dbName)) return array();
	$pdo = soyshop_cart_read_db($dbName);
	if(is_null($pdo)) return array();

	$stmt = $pdo->prepare("SELECT * FROM soyshop_orders ORDER BY idx ASC");
	$successed = $stmt->execute();
	if(!$successed) return array();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!count($results)) return array();

	$dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
	$items = array();
	foreach($results as $values){
		$idx = $values["idx"];
		unset($values["idx"]);
		$items[$idx] = $dao->getObject($values);
	}

	return $items;
}

//カートに入れた商品をデータベースに挿入する
function soyshop_cart_set_items($dbName = null, $items){
	if(is_null($dbName)) $dbName = soyshop_cart_init_db();
	if(is_null($dbName)) return null;

	// @ToDo バックアップを作成して、どうにかしたい
	$pdo = soyshop_cart_read_db($dbName);
	$pdo->query("delete from soyshop_orders");

	$stmt = $pdo->prepare(soyshop_cart_build_insert_statememt());

	$successed = true;
	if(count($items)){
		foreach($items as $idx => $item){
			$binds = array(
				":idx" => $idx,
				":item_id" => $item->getItemId(),
				":item_count" => $item->getItemCount(),
				":item_price" => $item->getItemPrice(),
				":total_price" => $item->getTotalPrice(),
				":item_name" => $item->getItemName(),
				":attributes" => $item->getAttributes(),
				":is_addition" => $item->getIsAddition()
			);
			try{
				$stmt->execute($binds);
			}catch(Exception $e){
				$successed = false;
			}
		}
	}

	// 1度でも失敗した場合はバックアップから引っ張り出してくる
	if(!$successed) {

	}

	return $dbName;
}

/**
 * 下記のSQL構文が自動生成される
 * CREATE TABLE soyshop_orders(
 *	idx integer not null UNIQUE,
 *	item_id integer not null,
 *	item_count integer not null,
 *	item_price integer not null,
 *	total_price integer not null,
 *	item_name integer not null,
 *	attributes varchar,
 *	is_addition integer default 0
 *);
 */
function soyshop_cart_build_table_sql(){
	$schemas = file_get_contents(SOY2::RootDir() . "logic/init/sqlite.sql");
	preg_match('/create table soyshop_orders([\s\S]*?);/', $schemas, $tmp);
	$sql = $tmp[0];

	//idカラムをidxカラムに変更する
	$sql = str_replace("id integer primary key AUTOINCREMENT", "idx integer not null", $sql);

	//不要なカラムは削除する
	$lines = explode("\n", $sql);
	$sql = "";
	foreach($lines as $line){
		if(!soyshop_cart_check_column($line)) continue;
		if(is_numeric(stripos($line, "unique"))) continue;
		if(stripos($line, "is_addition")) $line = str_replace(",", "", $line);
		$sql .= $line . "\n";
	}
	$sql = str_replace("idx integer not null", "idx integer not null UNIQUE", $sql);
	return trim($sql);
}

function soyshop_cart_build_insert_statememt(){
	$schemas = file_get_contents(SOY2::RootDir() . "logic/init/sqlite.sql");
	preg_match('/create table soyshop_orders([\s\S]*?);/', $schemas, $tmp);
	$sql = $tmp[0];

	$columns = array();
	$lines = explode("\n", $sql);
	foreach($lines as $line){
		$line = trim($line);
		if(
			stripos($line, "create") === 0 ||
			stripos($line, "unique") === 0 ||
			stripos($line, ")") === 0 ||
			!soyshop_cart_check_column($line)
		) continue;
		$columns[] = trim(substr($line, 0, strpos($line, " ")));
	}

	return "INSERT INTO soyshop_orders(idx," . implode(",", $columns) . ") VALUES (:idx,:" . implode(",:", $columns) . ");";
}

function soyshop_cart_check_column($line){
	$line = trim($line);
	//不要なカラム
	foreach(array("id ", "order_id", "status", "flag", "cdate", "is_sended", "is_confirm", "display_order") as $c){
		if(stripos($line, $c) === 0) return false;
	}
	return true;
}

//注文が終了したらデータベースを削除
function soyshop_cart_delete_db($dbName){
	$path = soyshop_cart_db_directory() . $dbName . ".db";
	if(file_exists($path)){
		unlink($path);
	}
}

//古いデータベースは定期的に削除する 先日以前に作成したdbを一斉に削除
function soyshop_cart_routine_delete_db(){
	$dir = soyshop_cart_db_directory();
	$files = soy2_scandir($dir);
	if(!count($files)) return;

	foreach($files as $file){
		$path = $dir . $file;
		if(filemtime($path) < strtotime("-1 day")){	//昨日作成したdbは削除する
			unlink($path);
		}
	}
}

function soyshop_cart_db_directory(){
	$dir = SOYSHOP_SITE_DIRECTORY . ".cart/";
	if(!file_exists($dir)) mkdir($dir);
	return $dir;
}

/** 便利な関数 **/
function soyshop_cart_get_item_price($dbName){
	if(is_null($dbName)) return 0;
	$pdo = soyshop_cart_read_db($dbName);
	if(is_null($pdo)) return 0;

	$stmt = $pdo->prepare("SELECT SUM(total_price) AS TOTAL FROM soyshop_orders");
	$successed = $stmt->execute();
	if(!$successed) return 0;

	$res = $stmt->fetch();

	return (isset($res["TOTAL"]) && is_numeric($res["TOTAL"])) ? (int)$res["TOTAL"] : 0;
}

function soyshop_cart_get_item_count($dbName){
	if(is_null($dbName)) return 0;
	$pdo = soyshop_cart_read_db($dbName);
	if(is_null($pdo)) return 0;

	$stmt = $pdo->prepare("SELECT SUM(item_count) AS TOTAL FROM soyshop_orders");
	$successed = $stmt->execute();
	if(!$successed) return 0;

	$res = $stmt->fetch();

	return (isset($res["TOTAL"]) && is_numeric($res["TOTAL"])) ? (int)$res["TOTAL"] : 0;
}
