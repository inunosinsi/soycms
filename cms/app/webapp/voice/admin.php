<?php


class SOYVoiceApplication{

	function init(){

		/**
		 * タブの設定
		 */
		CMSApplication::setTabs(array(
			array(
				"label" => "新着",
				"href" => SOY2PageController::createLink("voice")
			),
			array(
				"label" => "コメント",
				"href" => SOY2PageController::createLink("voice.Comment")
			),
			array(
				"label" => "設定",
				"href" => SOY2PageController::createLink("voice.Config")
			),
			array(
				"label" => "ヘルプ",
				"href" => SOY2PageController::createLink("voice.Help")
			),
		));

		CMSApplication::main(array($this,"main"));

//		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/list/css/style.css"));
//		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/list/js/jquery.js"));
//		CMSApplication::addScript("",'jQuery.noConflict();');
//		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/list/js/accordion.js"));
		

		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page","PagePlugin");
		SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");

		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/voice.db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		//画像の置き場を生成
		$path = SOY_VOICE_IMAGE_UPLOAD_DIR;
		if(!is_dir($path))mkdir($path,0755);
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
		if(preg_match('/^Comment/',$classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^Config/',$classPath)){
			CMSApplication::setActiveTab(2);
		}
		if(preg_match('/^Help/',$classPath)){
			CMSApplication::setActiveTab(3);
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

$app = new SOYVoiceApplication();
$app->init();
?>
