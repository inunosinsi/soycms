<?php
/**
 * 管理画面側のコントローラ
 */
class StepMailApplication{

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
				"label" => "ホーム",
				"href" => SOY2PageController::createLink(APPLICATION_ID)
			),
			array(
				"label" => "ステップメール",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Mail")
			),
			array(
				"label" => "登録者",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".User")
			),
			array(
				"label" => "設定",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Config")
			),
		));

		CMSApplication::main(array($this, "main"));

		//外部CSSと外部JSファイルの読み込みを指定します。
//		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/css/style.css"));
//		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/js/app.js"));

		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page", "PagePlugin");
		SOY2HTMLPlugin::addPlugin("link", "LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display", "DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel", "PanelPlugin");
		
		//DBの初期化を行う。データベースを使用したい場合はコメントアウトを外してください。
		$initLogic = SOY2Logic::createInstance("logic.Init.InitLogic");
		if($initLogic->check()){
			$initLogic->init();
		}
		
		unset($initLogic);
		
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
		if(preg_match('/^Mail/', $classPath)){
			CMSApplication::setActiveTab(1);
		}
		
		if(preg_match('/^User/', $classPath)){
			CMSApplication::setActiveTab(2);
		}
		
		if(preg_match('/^Config/', $classPath)){
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

$app = new StepMailApplication();
$app->init();
?>