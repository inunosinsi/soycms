<?php

class ItemBlockConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.item_block.util.ItemBlockUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			ItemBlockUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		$config = ItemBlockUtil::getConfig();
		self::createBlockModule($config["count"]);

		parent::__construct();

		$this->addForm("form");

		$this->addInput("count", array(
			"name" => "Config[count]",
			"value" => (int)$config["count"],
			"style" => "width:80px;"
		));

		DisplayPlugin::toggle("module_description", $config["count"] > 0);
		$this->addLabel("range", array(
			"text" => ($config["count"] > 1) ? "1〜" . $config["count"] : 1
		));
	}

	//モジュールを作成する
	private function createBlockModule($count){
		$count = (int)$count;
		if($count === 0) return;	//何もしない

		//サンプルをモジュールディレクトリにコピーする
		$from = dirname(dirname(__FILE__)) . "/sample/sample.txt";
		$to = SOYSHOP_SITE_DIRECTORY . "/.module/parts/";
		if(!file_exists($to)) mkdir($to);
		$to .= "item_block_plugin.php";
		if(!file_exists($to)) copy($from, $to);

		//@ToDo countに合わせて、item_block_categoryを複製する
		$src = file_get_contents(dirname(dirname(__FILE__)) . "/sample/copy.txt");
		for($i = 1; $i <= $count; $i++){
			$code = str_replace("#int#",  $i, $src);
			$to = SOYSHOP_SITE_DIRECTORY . "/.module/parts/item_block_plugin_" . $i . ".php";
			file_put_contents($to, $code);
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
