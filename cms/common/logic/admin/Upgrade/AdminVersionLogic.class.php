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
class AdminVersionLogic extends SOY2LogicBase{

	private $dir;

	//DataSets (soycms_admin_data_sets)でのclass名
	const VERSION_KEY = "SOYCMS_ADMIN_VERSION";

	/*
	 * 更新ファイルの正規表現
	 * 例：update-1.5.sql
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
	 * 現在のデータベースのバージョンを返す
	 * @return int
	 */
	public function getCurrentScriptVersion(){
		$files = $this->getUpdateFiles();
		return count($files) +1;
	}

	/**
	 * 現在のデータベースのバージョン番号を保存する
	 * @param string version
	 * @throw Exception
	 */
	public function registerCurrentScriptVersion(){
		$this->registerVersion($this->getCurrentScriptVersion());
	}

	/**
	 * 更新ファイルを取得する
	 * @return Array
	 */
	private function getUpdateFiles(){

		//ディレクトリの実在チェック
		if(strlen($this->dir) < 1){
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
