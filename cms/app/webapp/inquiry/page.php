<?php
define("APPLICATION_ID","inquiry");

/**
 * ページ表示
 */
class SOYInquiry_PageApplication{

	var $page;
	var $mailaddress;
	var $serverConfig;
	var $form;
	var $templateDir;
	var $pageUrl;

	function init(){
		CMSApplication::main(array($this, "main"));
	}

	function main($page){

		$this->page = $page;
		$questionMarkPosition = strpos($_SERVER["REQUEST_URI"], "?");
		$this->pageUrl = $questionMarkPosition !== false && $questionMarkPosition > 0
		               ? substr($_SERVER["REQUEST_URI"], 0, $questionMarkPosition)
		               : $_SERVER["REQUEST_URI"]
		               ;

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

		//定数設定
		$this->serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();
		define("SOY_INQUIRY_UPLOAD_DIR", rtrim(SOY_INQUIRY_UPLOAD_ROOT_DIR . "/" . trim($this->serverConfig->getUploadDir(), "/"), "/") . "/");
		define("SOY_INQUIRY_UPLOAD_TEMP_DIR", SOY_INQUIRY_UPLOAD_DIR . "tmp/");

		$arguments = CMSApplication::getArguments();

		/* メイン処理開始 */

		$this->page->createAdd("soyinquiry", "SOYInquiry_FormComponent", array(
			"application" => $this,
			"soy2prefix" => "app"
		));

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

	/**
	 * フォーム出力のメイン処理
	 */
	function getForm($formId){

		//フォームの使用を禁止しているユーザであるか？
		if(!isset($_GET["block"]) && SOY2Logic::createInstance("logic.InquiryLogic")->checkBanIpAddress()){
			SOY2PageController::redirect($this->pageUrl . "?block");
			exit;
		}

		try{
    		$dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    		$form = $dao->getByFormId($formId);
    		$this->form = $form;
	    }catch(Exception $e){
			return "";
		}

		//SOY Shop連携を行っているかチェック
		$connectConfig = $form->getConfigObject()->getConnect();
		$soyshopSiteId = ($connectConfig["siteId"] > 0) ? (int)$connectConfig["siteId"] : null;
		define("SOYINQUERY_SOYSHOP_CONNECT_SITE_ID", $soyshopSiteId);

		$config = $form->getConfigObject();
		$this->config = $config;

		//スマホカラムモード判定
		$this->defineSmartPhoneMode();

		//現在のページのURL（complete.phpなどで使用される）
		$page_link = $this->pageUrl;

		//template directory setting
		if(!defined("SOYSHOP_INQUIRY_FORM_THEME")) define("SOYSHOP_INQUIRY_FORM_THEME", $config->getTheme());
		$templateDir = SOYInquiryUtil::getTemplateDir(SOYSHOP_INQUIRY_FORM_THEME);
		$this->templateDir = $templateDir;

		//ブロックしている時の表示
		if(isset($_GET["block"])){
			ob_start();
			if(file_exists($templateDir . "ban.php")){
				include_once($templateDir . "ban.php");
			}else{
				include_once(SOY2::RootDir() . "template/_sample/ban.php");
			}
			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

	    $columnDAO = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
	    $columns = $columnDAO->getOrderedColumnsByFormId($form->getId());

	    //隠しvalueから入力値を復元する
	    if(isset($_POST["form_value"]) && isset($_POST["form_hash"])){
	    	$value = base64_decode($_POST["form_value"]);

	    	//不正な書き換えでない場合のみ
	    	if(md5($value) == $_POST["form_hash"]){
	    		$_POST["data"] = json_decode($value, true);
	    	}
	    }

	    //CAPTCHA画像出力
	    if(isset($_GET["captcha"])){

	    	header("Content-Type: image/jpeg");
			$captcha = str_replace(array(".", "/", "\\"), "", $_GET["captcha"]);
			echo file_get_contents(SOY2HTMLConfig::CacheDir() . $captcha . ".jpg");
			//CAPTCHA画像の削除
	    	@unlink(SOY2HTMLConfig::CacheDir() . $captcha . ".jpg");
	    	exit;

	    //CSS出力
	    }else if(isset($_GET["stylesheet"])){

			if(file_exists($templateDir . "style.php")){
		    	header("Content-Type: text/css; charset: UTF-8");
		    	include_once($templateDir . "style.php");
			}else{
				header("HTTP/1.1 404 Not Found");
			}
	    	exit;

	    //送信完了画面表示
	    }else if(isset($_GET["complete"])){
	    	$inquiry = null;
	    	if(empty($errors)){
	    		$inqdao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
	    		try{
	    			$inquiry = $inqdao->getByTrackingNumber($_GET["trackid"]);
	    			//とりあえずタイムアウトを30分にする！
	    			if(time() - $inquiry->getCreateDate() > 30 * 60){
	    				throw new Exception();
	    			}
	    		}catch(Exception $e){
	    			//tracking number が不正または一定時間が経過した
			    	SOY2PageController::redirect($this->pageUrl);
	    		}

				//IPアドレスによる使用制限


	    		ob_start();
				$this->outputCSS();
				include_once($templateDir . "complete.php");
		    	$html = ob_get_contents();
		    	ob_end_clean();

		    	return $html;
	    	}

	    //確定：値の保存、メール送信
	    }else if(isset($_POST["send"]) || isset($_POST["send_x"])){
			$this->checkBanMailAddress($_POST["data"], $columns);

			//Google reCAPTCHA v3を利用している場合はここで調べる
			if(isset($_POST["google_recaptcha"]) && strlen($_POST["google_recaptcha"])){
				if(defined("SOYSHOP_ID")){	//SOY Shop版
					$old = SOYInquiryUtil::switchSOYShopConfig(SOYSHOP_ID);
					SOY2::import("module.plugins.reCAPTCHAv3.util.reCAPTCHAUtil");
					$shopconf = reCAPTCHAUtil::getConfig();
					$secretKey = (isset($shopconf["secret_key"])) ? $shopconf["secret_key"] : "";
					SOYInquiryUtil::resetConfig($old);
				}else{	// SOY CMS版
					$obj = CMSPlugin::loadPluginConfig("re_captcha_v3");
					$secretKey = $obj->getSecretKey();
				}

				if(strlen($secretKey)){
					$reCapValues = array(
						"secret" => $secretKey,
						"response" => $_POST["google_recaptcha"]
					);
					$ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($reCapValues));
					curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
					$out = curl_exec($ch);

					$json = json_decode($out);
					//@ToDo scoreを見て挙動を確認する スコアは0.0〜1.0で0.5が人とボットの閾値
					if(!$json->success || $json->score < 0.5){
						//BANのページに飛ばす
						SOY2Logic::createInstance("logic.InquiryLogic", array("form" => $this->form))->banIPAddress($_SERVER["REMOTE_ADDR"]);
						SOY2PageController::redirect($this->pageUrl . "?block");
						exit;
					}
				}
			}

	    	$captcha_filename = str_replace(array(".", "/", "\\"), "", $_POST["data"]["hash"]);
	    	$captcha_value = (isset($_POST["captcha_value"])) ? md5($_POST["captcha_value"]) : "";
	    	$captcha = (isset($_POST["data"]["captcha"])) ? str_replace(array(".", "/", "\\"), "", $_POST["data"]["captcha"]) : "";

	    	if(!$config->getIsUseCaptcha() || ($captcha !== "" && $captcha == $captcha_value)){

		    	$errors = $this->checkPostData($_POST["data"], $columns);

		    	//問い合わせを追加＆メール送信
				$inquiry = $this->addInquiry($form->getId(), $columns, $_POST["data"], $this->pageUrl);

		    	@include_once($templateDir . "send.php");

		    	//リダイレクト
		    	SOY2PageController::redirect($this->pageUrl . "?complete&trackid=" . $inquiry->getTrackingNumber());
		    	exit;
	    	}else{
	    		//確認画面を表示させる
	    		$_POST["confirm"] = 1;
	    	}

	    //フォームに戻る
	    }else if(isset($_POST["form"]) || isset($_POST["form_x"])){
	    	$errors = $this->checkPostData($_POST["data"], $columns);

	    //テスト：郵便番号検索を含む
	    }else if(isset($_POST["test"]) || isset($_POST["test_x"])){
	    	$errors = $this->checkPostData($_POST["data"], $columns);
	    	$errors = array();	//エラーデータは空にする
	    }

	    //確認画面表示（Captcha判定に失敗したときのためにこのIF文は分離しておく必要がある）
		if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){
			$errors = self::checkPostData($_POST["data"], $columns);

			if(empty($errors)){

				//CAPTCHAを使用する場合
				if($config->getIsUseCaptcha() && function_exists("imagejpeg")){

					//CAPTCHAの生成
					$captcha_filename = str_replace(array(".", "/", "\\"), "", $_POST["data"]["hash"]);
					$captcha_value = $this->getRandomString(5);
					$captcha_hash = md5($captcha_value);

					//画像ファイル生成
					$this->generateCaptchaImage($captcha_value, $captcha_filename);

					//POSTデータに埋め込む
					$_POST["data"]["captcha"] = $captcha_hash;

					//CAPTCHA画像のURLを作成
					$captcha_url = $this->pageUrl . "?captcha=" . $captcha_filename;
				}

				$hidden_hash = md5(json_encode($_POST["data"]));
				$hidden_value = base64_encode(json_encode($_POST["data"]));

				$hidden_forms = '<input type="hidden" name="form_hash" value="' . $hidden_hash . '" />';
				$hidden_forms.= '<input type="hidden" name="form_value" value="' . $hidden_value . '" />';

				ob_start();
				$this->outputCSS();
		    	include_once($templateDir . "confirm.php");
		    	$html = ob_get_contents();
		    	ob_end_clean();

		    	//pop before smtp
		    	$mailLogic = SOY2Logic::createInstance("logic.MailLogic", array(
					"serverConfig" => $this->serverConfig,
					"formConfig" => $this->form->getConfigObject()
				));
		    	$mailLogic->prepare();

		    	return $html;
			}
		}

		//ランダムな値を作成
		$random_hash = md5(mt_rand());

		SOYInquiryUtil::setParameters();

		ob_start();
		$this->outputCSS();
    	include_once($templateDir . "form.php");
    	$html = ob_get_contents();
    	ob_end_clean();

		return $html;
	}

	/**
	 * POSTされた値をチェックする
	 */
	private function checkPostData($data, $columns){
		$errors = array();

		foreach($columns as $column){

			$id = $column->getId();

			if(isset($data[$id])){
				$column->setValue(@$data[$id]);
			}
			if(isset($data[$column->getColumnId()])){
				$column->setValue(@$data[$column->getColumnId()]);
			}
			$obj = $column->getColumn($this->form);

			//エラーチェック　連番の場合は必須でもチェックしない
			if(get_class($obj) != "SerialNumberColumn" && false === $obj->validate()){
				$errors[$id] = $obj->getErrorMessage();
				$errors[$column->getColumnId()] = $errors[$id];
			}
			$column->setValue($obj->getValue());
		}

		return $errors;
	}

	function checkBanMailAddress($data, $columns){
		foreach($columns as $column){

			if(strpos($column->getType(), "MailAddress") !== false && $column->getRequire() == 1){
				if(isset($data[$column->getColumnId()])){
					//確認フォーム付きメールアドレスカラムの場合は配列の0番目の値になる。普通のメールアドレスの場合は値
					$mailAddress = (is_array($data[$column->getColumnId()])) ? trim($data[$column->getColumnId()][0]) : $data[$column->getColumnId()];
				}else if(isset($data[$column->getId()])){
					//上と同様
					$mailAddress = (is_array($data[$column->getId()])) ? trim($data[$column->getId()][0]) : $data[$column->getId()];
				}else{
					$mailAddress = null;
				}

				//メールアドレスカラムがあって、メールアドレスが空の場合は強制的にお問い合わせを止める
				if(strlen($mailAddress) === 0){
					SOY2PageController::redirect($this->pageUrl . "?block");
			    	exit;
				}

				//禁止したドメインによる制御
				$config = soy2_unserialize($column->getConfig());
				if(isset($config["ban_mail_domain"]) && strlen($config["ban_mail_domain"])){
					$bans = explode(",", $config["ban_mail_domain"]);
					foreach($bans as $ban){
						preg_match('/@' . $ban . '$/', $mailAddress, $tmp);
						if(isset($tmp[0]) && strlen($tmp[0])){
							//該当するメールアドレスの場合、IPアドレスもBANする
							SOY2Logic::createInstance("logic.InquiryLogic", array("form" => $this->form))->banIPAddress($_SERVER["REMOTE_ADDR"]);
							SOY2PageController::redirect($this->pageUrl . "?block");
					    	exit;
						}
					}
				}
			}
		}
	}

	/**
	 * 問い合わせを追加する（メール送信を含む）
	 *
	 */
	function addInquiry($formId, $columns, $data, $url){

		$logic = SOY2Logic::createInstance("logic.InquiryLogic", array(
			"form" => $this->form
		));

		$inquiry  = $logic->addInquiry($formId);
		$body = $logic->getInquiryBody($inquiry, $columns);
		$res = $logic->updateInquiry($inquiry, $body, $logic->buildCSVData($inquiry, $columns), $url);

		$mailBody = array();
		if($res){

			//イベント呼び出し
			$logic->invokeOnSend($inquiry, $columns);

			$inquiryMailBody = $logic->getInquiryMailBody($inquiry, $columns);

			//連番はここで値を更新
			foreach($columns as $column){
				if($column->getType() == "SerialNumber"){
					$config = soy2_unserialize($column->getConfig());
					if(!isset($config["serialNumber"]) || !is_numeric($config["serialNumber"])) $config["serialNumber"] = 1;

					//連番を更新する
					$config["serialNumber"]++;
					$column->setConfig($config);

					try{
						SOY2DAOFactory::create("SOYInquiry_ColumnDAO")->update($column);
					}catch(Exception $e){
						//
					}
				}
			}

			//管理者用メールボディ
			$mailBody[0] = $inquiryMailBody;
			if($this->form->getConfigObject()->getIsIncludeAdminURL()){
				$mailBody[0] .= "\r\n\r\n-- \r\n問い合わせへのリンク:\r\n" . $this->getInquiryLink($inquiry, $this->serverConfig) . "\r\n";
    		}

			//拡張 - 管理側のメール
			if(is_readable($this->templateDir . "mail.admin.php")){
				ob_start();
				include_once($this->templateDir . "mail.admin.php");
				$mailBody[0] .= ob_get_contents();
				ob_end_clean();
			}

			//ユーザー用メールボディ
			$mailBody[1] = $inquiryMailBody;

			//拡張 - ユーザ側のメール
			if(is_readable($this->templateDir . "mail.user.php")){
				ob_start();
				include_once($this->templateDir . "mail.user.php");
				$mailBody[1] .= ob_get_contents();
				ob_end_clean();
			}

			//メールを送る
		    $this->sendEmail($inquiry, $columns, $mailBody);

			//IPアドレス毎に禁止するべきか調べた上で禁止する
			$ipAddress = $_SERVER["REMOTE_ADDR"];
			if($logic->checkRecentInquiryCount($ipAddress)) $logic->banIPAddress($ipAddress);
		}

		return $inquiry;
	}

	/**
	 * メールを送る
	 */
	function sendEmail($inquiry, $columns, $mailBody){

		//メール送信用のロジック作成
		$mailLogic = SOY2Logic::createInstance("logic.MailLogic", array(
			"serverConfig" => $this->serverConfig,
			"formConfig" => $this->form->getConfigObject()
		));

		//ユーザへの通知メールを検索する
		$userMailAddress = array();
		foreach($columns as $column){
			if($column->getType() == "MailAddress" && strlen($column->getValue())){
				$userMailAddress[] = $column->getValue();
			}else if($column->getType() == "ConfirmMailAddress" && is_array($column->getValue())){
				$value = $column->getValue();
				$userMailAddr = (isset($value[0])) ? $value[0] : null;
				if(is_null($userMailAddr)) $userMailAddr = (isset($value[1])) ? $value[1] : null;
				if(isset($userMailAddr)) $userMailAddress[] = $userMailAddr;

				//$userMailAddress[] = (isset($value[0])) ? $value[0] : (isset($value[1])) ? $value[1] : null;
			}
		}
		$mailLogic->sendNotifyMail($columns, $userMailAddress, $mailBody);
	}

	/**
	 * ランダムな文字列を取得
	 */
	function getRandomString($length){

		$alpha = range(ord('A'), ord('Z'));

		$res = "";
		for($i=0; $i < $length; $i++){
			$res .= chr($alpha[array_rand($alpha)]);
		}

		return $res;
	}

	/**
	 * Inquiryへのリンク
	 */
	function getInquiryLink($inquiry,$serverConfig){
		$url = $serverConfig->getAdminUrl();
		if($url[strlen($url) - 1] != "/")$url .= "/";
		if(!preg_match('/index.php$/', $url))$url .= "index.php/";
		return $url . APPLICATION_ID . "/Inquiry/Detail/" . $inquiry->getId();
	}

	/**
	 * スタイルシートの出力
	 * 0.9.5以降はスタイルシートへのリンク
	 */
	function outputCSS($link = true){
		if($this->config->isOutputDesign() && file_exists($this->templateDir . "style.php") && method_exists($this->page, "getHeadElement")){
			if($link){
				$this->page->getHeadElement()->insertHTML("<link rel=\"stylesheet\" title=\"SOY_Inquiry\" type=\"text/css\" href=\"" . $this->pageUrl . "?stylesheet&amp;" . ((int)(time() / 100)) . "\" />\n");
			}else{
				$this->page->getHeadElement()->appendHTML("<style type=\"text/css\">\n" . (file_get_contents($this->templateDir . "style.php")) . "\n</style>\n");
			}
		}
	}

	/**
	 * スマホカラムモードの定数定義
	 */
	private function defineSmartPhoneMode(){
		$isSmartPhone = false;
		if($this->config->getIsSmartPhone()){
			$isSmartPhone = $this->checkIfAccessedBySmartPhone();
		}
		define("SOY_INQUIRY_SMARTPHONE_MODE", $isSmartPhone);
	}

	/**
	 * スマホカラムモード用のキャリア判定
	 */
	private function checkIfAccessedBySmartPhone(){
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "" ;

		if(stripos($agent,"iphone") !== false){
			$isSmartPhone = true;
		}elseif(stripos($agent,"mobile") !== false && stripos($agent,"safari") !== false){
			$isSmartPhone = true;
		}elseif(stripos($agent,"android") !== false){
			$isSmartPhone = true;
		}elseif(stripos($agent,"windows phone") !== false){
			$isSmartPhone = true;
		}else{
			$isSmartPhone = false;
		}

		return $isSmartPhone;
	}

	/**
	 * Captcha用の画像を生成してファイルに保存する
	 * 要GD（imagejpeg）
	 */
	private function generateCaptchaImage($captcha_value, $captcha_filename){
		SOY2::import("logic.SimpleCaptchaGenerator");
		$gen = SimpleCaptchaGenerator::getInstance();
		if(DIRECTORY_SEPARATOR == '\\'){
			//Windowsの場合：GDFONTPATHが効かないようだ
			$gen->setFonts(array(SOY2::RootDir() . "fonts/" . SOYINQUIRY_FONT_NAME));
		}else{
			putenv("GDFONTPATH=".str_replace("\\", "/", SOY2::RootDir() . "fonts/"));
			$gen->setFonts(array(SOYINQUIRY_FONT_NAME));
		}
		$gen->setBgRange(255, 255);
		$gen->setFgRange(0, 0);
		$gen->setBorderRange(0, 0);
		$gen->setMaxLineWidth(1);
		imagejpeg($gen->generate($captcha_value), SOY2HTMLConfig::CacheDir() . $captcha_filename . ".jpg");
	}

}

class SOYInquiry_FormComponent extends HTMLLabel{

	private $application;

	function execute(){
		parent::execute();
		$html = $this->application->getForm($this->getAttribute("app:formid"));
		$this->setHtml($html);
	}
	function getApplication() {
		return $this->application;
	}
	function setApplication($application) {
		$this->application = $application;
	}
}

$app = new SOYInquiry_PageApplication();
$app->init();
