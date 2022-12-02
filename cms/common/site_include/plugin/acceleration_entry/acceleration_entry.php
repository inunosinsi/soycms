<?php

class AccelerationEntryPlugin{

	const PLUGIN_ID = "acceleration_entry_plugin";

	private $isPutIndex = false;	//インデックスを張ったか？

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"記事詳細高速表示プラグイン",
			"type" => Plugin::TYPE_DB,
			"description"=>"記事の作成日をUNIQUEな値にして、表示速度を高速化する",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			//管理画面側
			if(!defined("_SITE_ROOT_")){
				//作成日に重複があれば値をずらす
				if(!$this->isPutIndex) self::checkDuplicateCdateAndAdjustment();

				CMSPlugin::addPluginConfigPage($this->getId(),array(
					$this,"config_page"
				));
			}
		}
	}

	private function checkDuplicateCdateAndAdjustment(){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT cdate FROM Entry GROUP BY cdate HAVING count(*) > 1 LIMIT 10");
		}catch(Excention $e){
			$res = array();
		}

		if(count($res) && isset($res[0]["cdate"])){
			foreach($res as $v){
				self::adjustment((int)$v["cdate"]);
			}

		//記事テーブルの作成日にインデックスを貼る
		}else if(!$this->isPutIndex){
			self::putIndex();
		}
	}

	private function adjustment($cdate){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id FROM Entry WHERE cdate = :cdate", array(":cdate" => $cdate));
		}catch(Excention $e){
			$res = array();
		}

		if(!count($res)) return;

		foreach($res as $v){
			if(!isset($v["id"])) continue;
			for(;;){
				try{
					$dao->executeUpdateQuery("UPDATE Entry SET cdate = :cdate WHERE id = :id", array(":cdate" => ++$cdate, ":id" => $v["id"]));
					break;
				}catch(Exception $e){
					//
				}
			}
		}
	}

	private function putIndex(){
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("CREATE UNIQUE INDEX cdate ON Entry(cdate)");
			$dao->executeUpdateQuery("CREATE UNIQUE INDEX alias ON Entry(alias)");
			$this->isPutIndex = true;
			CMSPlugin::savePluginConfig(self::PLUGIN_ID, $this);
		}catch(Exception $e){
			var_dump($e);
		}
	}

	function config_page(){
		return "記事の作成日にインデックスを張って、データ参照の高速化を行う(ベータ版)";
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(AccelerationEntryPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new AccelerationEntryPlugin();
		}

		CMSPlugin::addPlugin(AccelerationEntryPlugin::PLUGIN_ID, array($obj, "init"));
	}
}

AccelerationEntryPlugin::register();
