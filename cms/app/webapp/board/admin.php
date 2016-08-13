<?php

class SOYInquiryApplication{
	
	
	function init(){
		//タブを追加します。
		CMSApplication::setTabs(array(
			array(
				"label" => "掲示板一覧",
				"href" => SOY2PageController::createLink(APPLICATION_ID)	
			),
			array(
				"label" => "ヘルプ",
				"href" => SOY2PageController::createLink(APPLICATION_ID . ".Help")	
			)
		));
		
		CMSApplication::main(array($this,"main"));
		
		//設定の読み込み
		include_once(dirname(__FILE__) . "/config.php");
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		//SOY2HTMLの設定		
		SOY2HTMLPlugin::addPlugin("page","PagePlugin");
		SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");
		
		//CSSの読み込み
		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/inquiry/css/style.css"));

	}
	
	function main(){

		if(isset($_GET["bat"]) && file_exists(dirname(__FILE__) . "/src/bat/" . $_GET["bat"] . ".php")){
			include(dirname(__FILE__) . "/src/bat/" . $_GET["bat"] . ".php");
			exit;
		}
		
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
		if(preg_match('/^Inquiry/',$classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^Form/',$classPath)){
			CMSApplication::setActiveTab(2);
		}
		if(preg_match('/^Config/',$classPath)){
			CMSApplication::setActiveTab(3);
		}
		if(preg_match('/^Help/',$classPath)){
			CMSApplication::setActiveTab(4);
		}
		
		if(!SOY2HTMLFactory::pageExists($classPath)){
			return $classPath;
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

$app = new SOYInquiryApplication();
$app->init();

?>
