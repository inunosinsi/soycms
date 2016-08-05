<?php


class SOYListApplication{

	function init(){

		$level = CMSApplication::getAppAuthLevel();

		/**
		 * タブの設定
		 */
		CMSApplication::setTabs(array(
			array(
				"label" => "HOME",
				"href" => SOY2PageController::createLink("lpo")
			),
			array(
				"label" => "List",
				"href" => SOY2PageController::createLink("lpo.List")
			),
			array(
				"label" => "File",
				"href" => SOY2PageController::createLink("lpo.File")
			),
			array(
				"label" => "Config",
				"href" => SOY2PageController::createLink("lpo.Config"),
				"visible" => ($level == 1) ? true : false
			),
			array(
				"label" => "Help",
				"href" => SOY2PageController::createLink("lpo.Help"),
				"visible" => ($level == 1) ? true : false
			),
		));

		CMSApplication::main(array($this,"main"));

		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/lpo/css/style.css"));

		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page","PagePlugin");
		SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");

		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/lpo.db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		//画像の置き場を生成
		$path = SOY_LPO_IMAGE_UPLOAD_DIR;
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
		if(preg_match('/^List/',$classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^File/',$classPath)){
			CMSApplication::setActiveTab(2);
		}
		if(preg_match('/^Config/',$classPath)){
			CMSApplication::setActiveTab(3);
		}
		if(preg_match('/^Help/',$classPath)){
			CMSApplication::setActiveTab(4);
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

$app = new SOYListApplication();
$app->init();
?>
