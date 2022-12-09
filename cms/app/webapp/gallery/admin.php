<?php

class SOYGalleryApplication{

	function init(){

		$level = CMSApplication::getAppAuthLevel();

		/**
		 * タブの設定
		 */
		CMSApplication::setTabs(array(
			array(
				"label" => "ホーム",
				"href" => SOY2PageController::createLink(APPLICATION_ID),
				"icon" => "home"
			),
			array(
				"label" => "ギャラリ",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Gallery"),
				//"icon" => "gallery"
			),
			array(
				"label" => "ヘルプ",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Help"),
				"visible" => ($level == 1),
				"icon" => "question"
			),
		));

		CMSApplication::main(array($this, "main"));

		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page", "PagePlugin");
		SOY2HTMLPlugin::addPlugin("link", "LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display", "DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel", "PanelPlugin");

		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/gallery.db")){
			SOY2Logic::createInstance("logic.InitLogic")->init();
		}

		//データベースの更新:getでupgradeのindexが存在した場合に実行
		if(isset($_GET["upgrade"])){
			SOY2Logic::createInstance("logic.UpgradeLogic")->execute();
		}
	}

	function main(){

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
		$path = implode(".", $classPath);
		$classPath = $path;

		if(strlen($classPath) < 1)$classPath = "Index";
		$classPath .= 'Page';

		//一回だけIndexPageを試す。
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$classPath = $path;

			if(!preg_match('/.+Page$/', $classPath)){
				$classPath .= '.IndexPage';
			}
		}

		//タブの設定
		if(preg_match('/^Gallery/', $classPath) || preg_match('/^List/', $classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^Help/', $classPath)){
			CMSApplication::setActiveTab(2);
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
			//
		}

		return $html;
	}

}

$app = new SOYGalleryApplication();
$app->init();
