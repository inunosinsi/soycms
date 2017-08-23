<?php

SOY2::import("domain.admin.AdminDataSets");

class MailConfigLogic extends SOY2LogicBase{

	const CONFIG_KEY = "admin.mail_config";

	private $config;

	/*
	 * DSNの一時保存用
	 */
	private $dsn;
	private $user;
	private $pass;

	/**
	 * 設定取得
	 */
	public function get(){

		if(!$this->config){
			$this->load();
		}

		return $this->config;
	}

	/**
	 * 設定読み込み
	 */
	private function load(){
		//DSN切り替え（admin以外でも使えるように）
		$this->useAdminDsn();

		//取得
		try{
			$this->config = AdminDataSets::get(self::CONFIG_KEY);
		}catch(Exception $e){
			$this->config = new SOY2Mail_ServerConfig();
		}

		//DSN戻し
		$this->restoreDsn();
	}

	/**
	 * 設定保存
	 */
	public function save(SOY2Mail_ServerConfig $config){
		//DSN切り替え
		$this->useAdminDsn();

		//保存
		AdminDataSets::put(self::CONFIG_KEY, $config);

		//DSN戻し
		$this->restoreDsn();
	}

	/**
	 * admin用のデータベースを使う
	 */
	private function useAdminDsn(){
		//バックアップ
		$this->dsn = SOY2DAOConfig::dsn();
		$this->user = SOY2DAOConfig::user();
		$this->pass = SOY2DAOConfig::pass();

		//DSNを切り替え
		if(defined("SOYCMS_ASP_MODE")){
			//ASP用
			SOY2DAOConfig::dsn(SOYCMS_ASP_DSN);
			SOY2DAOConfig::user(SOYCMS_ASP_USER);
			SOY2DAOConfig::pass(SOYCMS_ASP_PASS);
		}else{
			//通常のadmin用
			SOY2DAOConfig::dsn(ADMIN_DB_DSN);
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}
	}

	/**
	 * 元のデータベースを使う
	 */
	private function restoreDsn(){
		//バックアップした値に戻す
		SOY2DAOConfig::dsn($this->dsn);
		SOY2DAOConfig::user($this->user);
		SOY2DAOConfig::pass($this->pass);
	}

}
