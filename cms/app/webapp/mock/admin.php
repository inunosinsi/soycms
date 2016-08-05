<?php
/**
 * 管理画面側のコントローラ
 */
class SOYMockApplication{

	function init(){

		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");

		/*
		 * ログイン時のアカウントの管理権限を見ます
		 * 1:管理者,0:操作者
		 */
		$level = CMSApplication::getAppAuthLevel();

		/*
		 * タブを追加します。
		 */
		CMSApplication::setTabs(array(
			array(
				"label" => "HOME",
				"href" => SOY2PageController::createLink(APPLICATION_ID)
			),
			array(
				"label" => "Sample",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Sample")	//""内にある.は/に置換されます
//				"visible" => ($level == 1)	//管理権限が与えられていないアカウントがログインした場合にタブを見せるかどうか
			),
			array(
				"label" => "Page",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Page"),
//				"visible" => ($level == 1)
			),
			array(
				"label" => "Help",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Help"),
//				"visible" => ($level == 1)
			),
		));

		CMSApplication::main(array($this, "main"));

		//外部CSSと外部JSファイルの読み込みを指定します。
		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/css/sample.css"));
		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/js/sample.js"));

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page", "PagePlugin");
		SOY2HTMLPlugin::addPlugin("link", "LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display", "DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel", "PanelPlugin");

		//DBの初期化を行う。データベースを使用したい場合はコメントアウトを外してください。
		if(!file_exists(CMS_COMMON . "db/" . APPLICATION_ID . ".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
//		//データベースの更新:getでupgradeのindexが存在した場合に実行
//		if(isset($_GET["upgrade"])){
//			$logic = SOY2Logic::createInstance("logic.UpgradeLogic");
//			$logic->execute();
//		}
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

		if(strlen($classPath) < 1) $classPath = "Index";
		$classPath .= 'Page';

		//一回だけIndexPageを試す。
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$classPath = $path;

			if(!preg_match('/.+Page$/', $classPath)){
				$classPath .= '.IndexPage';
			}
		}

		//タブの設定
		if(preg_match('/^Sample/', $classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^Page/', $classPath)){
			CMSApplication::setActiveTab(2);
		}
		if(preg_match('/^Help/', $classPath)){
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
			//
		}

		return $html;
	}
}

$app = new SOYMockApplication();
$app->init();
?>