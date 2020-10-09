<?php

class SOYShopApplication{

	function init(){

		//設定の読み込み（ログイン権限チェック）
		include_once(dirname(__FILE__) . "/config.php");

		//権限があるか？調べる
		if(!UserInfoUtil::isDefaultUser()){
			$auth = CMSApplication::getAppAuthLevel();
			if($auth == 0 || $auth == 2){	//権限なし
				header("Location:" . rtrim(dirname(CMSApplication::getRoot()), "/") . "/admin/");
				exit;
			}
		}

		/**
		 * タブの設定
		 */
		$tabs = array();

		//App操作者にはトップだけ
		$tabs[] = array(
			"label" => "HOME",
			"href" => SOY2PageController::createLink("shop"),
			"icon" => "home"
		);

		//App管理者、初期管理者には以下のタブも見せる
		if(CMSApplication::checkAuthSuperUser()){
			$tabs[] = array(
				"label" => "新規作成",
				"href" => SOY2PageController::createLink("shop.Create"),
				"icon" => "plus-circle"
			);
			$tabs[] = array(
				"label" => "サイト設定",
				"href" => SOY2PageController::createLink("shop.Site"),
				"icon" => "sitemap"
			);
			$tabs[] = array(
				"label" => "権限設定",
				"href" => SOY2PageController::createLink("shop.Config"),
				"icon" => "user"
			);
		}

		CMSApplication::setTabs($tabs);

		CMSApplication::main(array($this,"main"));

		CMSApplication::addLink(SOY2PageController::createRelativeLink("./webapp/shop/css/style.css"));
		CMSApplication::addScript(SOY2PageController::createRelativeLink("./webapp/shop/js/common.js"));


		//SOY2HTMLの設定
		SOY2HTMLPlugin::addPlugin("page","PagePlugin");
		SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
		SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
		SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");
		SOY2HTMLPlugin::addPlugin("error","DisplayErrorPlugin");

		//App管理者、初期管理者だけ見える部分
		DisplayPlugin::toggle("for_super_user", CMSApplication::checkAuthSuperUser());

		//共通のクラスを読み込む


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

		//アクセス制限
		if(CMSApplication::checkAuthSuperUser()){
			//初期管理者、App管理者
		}else{
			//App操作者
			$classPath = 'IndexPage';
		}

		//タブの設定
		if(preg_match('/^Shop/',$classPath)){
			CMSApplication::setActiveTab(0);
		}
		if(preg_match('/^Create/',$classPath)){
			CMSApplication::setActiveTab(1);
		}
		if(preg_match('/^Site/',$classPath)){
			CMSApplication::setActiveTab(2);
		}
		if(preg_match('/^Config/',$classPath)){
			CMSApplication::setActiveTab(3);
		}

		if(!SOY2HTMLFactory::pageExists($classPath)){
			return "";
		}

		$webPage = &SOY2HTMLFactory::createInstance($classPath, array(
			"arguments" => $args
		));

		if(method_exists($webPage,"getErrors")){
			$errors = $webPage->getErrors();
			if(count($errors)>0){
				DisplayPlugin::visible("errors");
				DisplayErrorPlugin::setErrors($errors);
			}else{
				DisplayPlugin::hide("errors");
			}

		}

		$webPage->execute();
		return $webPage->getObject();

	}

}

$app = new SOYShopApplication();
$app->init();
