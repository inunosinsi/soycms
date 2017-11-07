<?php

/**
 * データベースの半自動更新ロジック
 *
 * 使用例
 * $logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
 * 		"target" => "admin"
 * ));
 * $logic->update();
 */
class UpdateAdminLogic extends SOY2LogicBase{

	private $dir;

	//DataSets (soycms_admin_data_sets)でのclass名
	const VERSION_KEY = "SOYCMS_ADMIN_VERSION";

	/*
	 * 更新ファイルの正規表現
	 * 例：update-1.5.php
	 */
	const UPDATE_FILE_REGEX = "/^update-([.0-9]+)\\.php\$/";

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		SOY2DAOFactory::importEntity("admin.AdminDataSets");
		$this->dir = SOY2::RootDir() . "admin/update/";
	}

	/**
	 * データベースを更新する
	 */
	public function update() {

		//現在のデータベースのバージョンを取得する
		$current_version = $this->getCurrentVersion();

		//updateのSQLファイルを取得する
		$script_files = $this->getUpdateFiles();

		//現在のデータベースのバージョンより上のupdateを実行する
		foreach($script_files as $version => $script_file){
			if($version > $current_version && strpos($script_file,".php")!==false){
				$this->executeScriptFile($script_file);
				$this->registerVersion($version);
			}
		}

	}

	/**
	 * 現在のデータベースのバージョンを返す
	 * @return int
	 */
	public function getCurrentVersion(){

		try{
			$version = AdminDataSets::get(self::VERSION_KEY);
		}catch(Exception $e){
			$version = null;
		}

		return $version;
	}

	/**
	 * 更新ファイルの最大バージョンを返す
	 * @return int
	 */
	public function getUpdateVersion(){
		$files = $this->getUpdateFiles();
		if($files && is_array($files) && count($files)){
			$files = array_keys($files);
			return array_pop($files);
		}
		return 0;
	}

	/**
	 * 更新が必要かどうか
	 * @return boolean
	 */
	public function hasUpdate(){
		$current = $this->getCurrentVersion();
		$update  = $this->getUpdateVersion();
		return ($update > 0 && $update > $current);
	}

	/**
	 * 更新ファイルを取得する
	 * @return Array
	 */
	private function getUpdateFiles(){

		//ディレクトリの実在チェック
		if(strlen($this->dir)<1){
			throw new Exception("'dir' is empty.");
		}
		if(!is_dir($this->dir)){
			throw new Exception("'dir' ({$this->dir}) does not exist.");
		}

		//ファイル一覧を取得する
		$sql_files = array();
		$match = array();
		$files = scandir($this->dir);
		foreach($files as $file){
			if("." == $file)continue;
			if(".." == $file)continue;
			if(preg_match(self::UPDATE_FILE_REGEX, $file, $match)){
				$script_files[(int)$match[1]] = $file;
			}
		}

		//versionの昇順に並べ替える
		ksort($script_files);

		return $script_files;
	}

	/**
	 * 指定したファイルのスクリプトを実行
	 */
	private function executeScriptFile($file){
		//実行する
		include_once($this->dir . $file);
	}

	/**
	 * バージョン番号を保存する
	 * @param string version
	 */
	private function registerVersion($version){
		try{
			AdminDataSets::put(self::VERSION_KEY,$version);
		}catch(Exception $e){
			error_log(var_export($e,true));
		}
	}
}
