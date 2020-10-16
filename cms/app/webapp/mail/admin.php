<?php

if(!defined("APPLICATION_ID")) define('APPLICATION_ID', "mail");

class SOYMailApplication{

	function init(){

		$level = CMSApplication::getAppAuthLevel();

		/**
		 * タブの設定
		 */
		CMSApplication::setTabs(array(
			array(
				"label" => "レポート",
				"href" => SOY2PageController::createLink(APPLICATION_ID),
				"icon" => "file"
			),
			array(
				"label" => "メール",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Mail"),
				"icon" => "envelope"
			),
			array(
				"label" => "ユーザ",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".User"),
				"icon" => "users"
			),
			array(
				"label" => "設定",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Config"),
				"visible" => ($level == 1) ? true : false,
				"icon" => "gear"
			),
			array(
				"label" => "SOYShop連携",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Connect"),
				"visible" => ($level == 1) ? true : false,
				"icon" => "arrows-alt"
			),
			array(
				"label" => "ログ",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Log"),
				"visible" => ($level == 1) ? true : false,
				"icon" => "database"
			),
			array(
				"label" => "ヘルプ",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Help"),
				"visible" => ($level == 1) ? true : false,
				"icon" => "question"
			)
		));

		CMSApplication::main(array($this,"main"));
		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/css/three.css"));
		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/js/advanced_textarea.js"));
		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/js/three.js"));

		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");

		//DBの初期化を行う
		if(!file_exists(SOYMAIL_DB_FILE)){
			SOY2Logic::createInstance("logic.InitLogic", array(
				"initCheckFile" => SOYMAIL_DB_FILE,
			))->init();
		}

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page","PagePlugin");
		SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");

		//共通のクラスを読み込む
		SOY2::import("domain.Area");
		SOY2::import("domain.SOYMailLog");
	}

	function main(){

		//廃止
		// if(isset($_GET["bat"]) && file_exists(dirname(__FILE__) . "/src/bat/" . $_GET["bat"] . ".php")){
		// 	include(dirname(__FILE__) . "/src/bat/" . $_GET["bat"] . ".php");
		// 	exit;
		// }

		$arguments = CMSApplication::getArguments();

		$classPath = array();
		$args = array();
		$flag = false;
		foreach($arguments as $key => $value){
			if(is_numeric($value)){
				$flag = true;
			}

			if($flag){
				$args[] = $value;
			}else{
				$classPath[] = $value;
			}
		}
		$path = implode(".",$classPath);
		$classPath = $path;

		if(strlen($classPath)<1)$classPath = "Index";
		$classPath .= 'Page';

		//一回だけIndexPageを試す。
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$classPath = $path;

			if(!preg_match('/.+Page$/',$classPath)){
				$classPath .= '.IndexPage';
			}
		}

		//タブの設定
		if(preg_match('/^Mail/',$classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^User/',$classPath)){
			CMSApplication::setActiveTab(2);
		}
		if(preg_match('/^Config/',$classPath)){
			CMSApplication::setActiveTab(3);
		}
		if(preg_match('/^Connect/',$classPath)){
			CMSApplication::setActiveTab(4);
		}
		if(preg_match('/^Log/',$classPath)){
			CMSApplication::setActiveTab(5);
		}
		if(preg_match('/^Help/',$classPath)){
			CMSApplication::setActiveTab(6);
		}

		if(!SOY2HTMLFactory::pageExists($classPath)){
			return "";
		}

		$webPage = &SOY2HTMLFactory::createInstance($classPath, array(
			"arguments" => $args
		));

		try{
			ob_start();
			$webPage->display();
			$html = ob_get_contents();
			ob_end_clean();
		}catch(Exception $e){
			var_dump($e);
		}

		return $html;
	}
}

$app = new SOYMailApplication();
$app->init();
