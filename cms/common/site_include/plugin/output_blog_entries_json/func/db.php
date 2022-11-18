<?php
/**
 * @return string
 */
function soycms_oje_db_path(){
    return _SITE_ROOT_ . "/.cache/oje.db";
}

/**
 * @return bool
 */
function soycms_oje_init_db(){

	//データベースの名前はアカウントIDにする
	$path = soycms_oje_db_path();
	if(!file_exists($path)){
		touch($path);
		$pdo = soycms_oje_read_db();

		//初期化
		$sqls = preg_split('/CREATE TABLE/', file_get_contents(SOY2::RootDir() . "site_include/plugin/output_blog_entries_json/sql/db.sql"), -1, PREG_SPLIT_NO_EMPTY) ;
		foreach($sqls as $sql){
			$pdo->query("CREATE TABLE " . trim($sql));
		}
	}

	return true;
}

/**
 * @return PDO
 */
function soycms_oje_read_db(){
    $path = soycms_oje_db_path();
    if(!file_exists($path)) touch($path);

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

/**
 * @return PDO
 */
function soycms_oje_pdo(){
    $path = soycms_oje_db_path();
	if(!file_exists($path)) soycms_oje_init_db();
	return soycms_oje_read_db();
}

//データベースを削除
function soycms_oje_delete_db(){
	$path = soycms_oje_db_path();
	if(file_exists($path)) unlink($path);
}

/**
 * データが登録されているか？を調べる
 * @return bool
 */
function soycms_oje_entry_exsits(){
	$stmt = soycms_oje_pdo()->prepare("SELECT id FROM oje_entries LIMIT 1");
	$successed = $stmt->execute();
	if(!$successed) return false;

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return (isset($results[0]["id"]));
}

/**
 * データを取得
 * @param int, int
 * @return array
 */
function soycms_oje_get_entries(int $lm=15, int $offset=0){
	$stmt = soycms_oje_pdo()->prepare("SELECT data FROM oje_entries ORDER BY cdate DESC LIMIT " . $lm . " OFFSET " . $offset);
	$successed = $stmt->execute();
	if(!$successed) return array();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!count($results)) return array();

	$arr = array();
	foreach($results as $r){
		$arr[] = soy2_unserialize($r["data"]);
	}

	return $arr;
}

/**
 * データを取得
 * @return array
 */
function soycms_oje_get_total(){
	$stmt = soycms_oje_pdo()->prepare("SELECT COUNT(id) AS CNT FROM oje_entries");
	$successed = $stmt->execute();
	if(!$successed) return 0;

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!count($results)) return 0;

	return (isset($results[0]["CNT"])) ? (int)$results[0]["CNT"] : 0;
}

/**
 * データを登録
 * @param array
 */
function soycms_oje_set_entry(array $values){
	$stmt = soycms_oje_pdo()->prepare("INSERT INTO oje_entries(cdate, udate, data) VALUES(:cdate, :udate, :data)");
	$stmt->execute(array(":cdate" => $values["cdate"], ":udate" => $values["udate"], ":data" => soy2_serialize($values)));
}