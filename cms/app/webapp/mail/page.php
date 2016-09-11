<?php

if(!defined("APPLICATION_ID")) define('APPLICATION_ID', "mail");

/**
 * ページ表示
 */
class SOYMail_PageApplication{
	
	var $page;
	var $mailaddress;
	
	function init(){
		
		CMSApplication::main(array($this,"main"));
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/soymail.db")){
			return;
		}
	}
	
	function main($page){
		
		$this->page = $page;
		
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();
		
		//設定ファイルの読み込み
		include_once(dirname(__FILE__) . "/config.php");
		SOY2::imports("form.*");
		
		$arguments = CMSApplication::getArguments();
		
		$flashSession = SOY2ActionSession::getFlashSession();
		
		/* メイン処理開始 */
		if(isset($_POST["register"]) && @$_POST["sid"] == session_id() && strlen($_POST["mailaddress"]) > 0){
			
			$mailaddress = $_POST["mailaddress"];
						
			/**
			 * メールアドレスのチェック:一応全角のアドレスを登録できる
			 * 条件 : @を含む文字列且つ、文字列にスペースが含まれないこと且つ、@は一文字
			 */
			if(!preg_match("/^(.+)@(.+)+$/",$mailaddress) || preg_match("/[\s|　]/",$mailaddress) || substr_count($mailaddress,"@") > 1){
				$flashSession->resetFlashCounter();
				$flashSession->setAttribute("soymail_mailaddress",$mailaddress);
				header("Location: ".SOY2PageController::createLink("",true) . $this->page->page->getUri() . "?failed");
				exit;
			}
			
			$name = (isset($_POST["name"])) ? $_POST["name"] : "";
			$attribute1 = (isset($_POST["attribute1"])) ? $_POST["attribute1"] : "";
			$attribute2 = (isset($_POST["attribute2"])) ? $_POST["attribute2"] : "";
			$attribute3 = (isset($_POST["attribute3"])) ? $_POST["attribute3"] : "";
		

			$userDAO = SOY2DAOFactory::create("SOYMailUserDAO");
			try{
				$userDAO->getIdByEmail($mailaddress);
				//既に登録している通知なしにスルーし、処理を終了する
			}catch(Exception $e){
				$user = new SOYMailUser();
				$user->setMailAddress($mailaddress);
				$user->setName($name);
				$user->setAttribute1($attribute1);
				$user->setAttribute2($attribute2);
				$user->setAttribute3($attribute3);
				$user->setRegisterDate(time());
				try{
					$userDAO->insert($user);
				}catch(Exception $e){
					//
				}
			}			
			
			$flashSession->resetFlashCounter();
			$flashSession->setAttribute("soymail_mailaddress",$mailaddress);
						
			header("Location: ".SOY2PageController::createLink("",true) . $this->page->page->getUri() . "?register");
			exit;
		}
		
		if(isset($_POST["unregister"]) && @$_POST["sid"] == session_id() && strlen($_POST["mailaddress"]) > 0){
			$mailaddress = $_POST["mailaddress"];
			
			$userDAO = SOY2DAOFactory::create("SOYMailUserDAO");
			try{
				$userId = $userDAO->getIdByEmail($mailaddress);
				$userDAO->delete($userId);
			}catch(Exception $e){
				
			}
			
			$flashSession->resetFlashCounter();
			$flashSession->setAttribute("soymail_mailaddress",$mailaddress);
			header("Location: ".SOY2PageController::createLink("",true) . $this->page->page->getUri() . "?unregister");
			exit;
		}
		
		$formVisble = (!isset($_GET["register"]) && !isset($_GET["unregister"]));
		
		//メールアドレスの取得
		$this->mailaddress = $flashSession->getAttribute("soymail_mailaddress");
		
		//メールアドレスが無い時
		if(!$formVisble && strlen($this->mailaddress) < 1){
			header("Location: ".SOY2PageController::createLink("",true) . $this->page->page->getUri());
			exit;
		}
				
		$this->outputRegisterForm($formVisble);
		$this->outputRegisterMessage(isset($_GET["register"]));
		$this->outputUnRegisterForm($formVisble);
		$this->outputUnRegisterMessage(isset($_GET["unregister"]));
		
		
		/* メイン処理ここまで */
				
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2HTMLConfig::CacheDir($oldCacheDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);
		
	}
	
	function outputRegisterForm($flag){
		
		$this->page->createAdd("register_form","RegisterForm",array(
			"visible" => $flag,
			"mailaddress" => $this->mailaddress,
			"soy2prefix" => "app"
		));
	}
	
	function outputRegisterMessage($flag){
		
		$this->page->createAdd("register_message","RegisterMessage",array(
			"visible" => $flag,
			"mailaddress" => $this->mailaddress,
			"soy2prefix" => "app"
		));
	}
	
	function outputUnRegisterForm($flag){
		
		$this->page->createAdd("unregister_form","UnRegisterForm",array(
			"visible" => $flag,
			"soy2prefix" => "app"
		));
	}
	
	function outputUnRegisterMessage($flag){
		$this->page->createAdd("unregister_message","RegisterMessage",array(
			"visible" => $flag,
			"mailaddress" => $this->mailaddress,
			"soy2prefix" => "app"
		));
	}
	
	
}

$app = new SOYMail_PageApplication();
$app->init();
?>