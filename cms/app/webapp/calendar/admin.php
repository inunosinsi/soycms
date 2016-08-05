<?php


class SOYCalendarApplication{

	function init(){

		/**
		 * タブの設定
		 */
		CMSApplication::setTabs(array(
			array(
				"label" => "Schedule",
				"href" => SOY2PageController::createLink("calendar")
			),
			array(
				"label" => "Title",
				"href" => SOY2PageController::createLink("calendar.Title")
			),
			array(
				"label" => "Archives",
				"href" => SOY2PageController::createLink("calendar.Archives")
			),
			array(
				"label" => "Help",
				"href" => SOY2PageController::createLink("calendar.Help")
			),
		));

		CMSApplication::main(array($this,"main"));

		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/calendar/css/style.css"));
		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/calendar/js/repeat.js"));
		

		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page","PagePlugin");
		SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");

		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/calendar.db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		//データベースの更新:getでupgradeのindexが存在した場合に実行
		if(isset($_GET["upgrade"])){
			$logic = SOY2Logic::createInstance("logic.UpgradeLogic");
			$logic->execute();
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
		if(preg_match('/^Title/',$classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^Archives/',$classPath)){
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

$app = new SOYCalendarApplication();
$app->init();
?>
