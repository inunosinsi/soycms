<?php

define('ADMIN_BLOCK_MANAGER',"admin_block_manager");

//初期化
$obj = CMSPlugin::loadPluginConfig(ADMIN_BLOCK_MANAGER);
if(is_null($obj)){
	$obj = new AdminBlockManagerPlugin();
}
CMSPlugin::addPlugin(ADMIN_BLOCK_MANAGER,array($obj,"init"));

class AdminBlockManagerPlugin{

	function getId(){
		return ADMIN_BLOCK_MANAGER;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ブロック一括管理プラグイン",
			"description"=>"複数ページに対して、ブロックの追加や変更が出来ます。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));
	}

	function config_page(){
		if(CMSPlugin::activeCheck($this->getId())){
			include_once(dirname(__FILE__)."/index.php");
			return get_config_page();
		}

		return "";
	}
}
