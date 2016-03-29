<?php
SOY2::import("logic.mail.MailLogic");
/**
 * 即時配信実行ページ。
 * 送信後には送信履歴詳細ページへリダイレクト
 */
class SendNowPage extends WebPage{

	var $serverConfig;

    function SendNowPage($args) {
    	$id = $args[0];

    	$mailDAO = SOY2DAOFactory::create("MailDAO");
	    $mail = $mailDAO->getById($id);

		$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();
		$this->serverConfig = $serverConfig;

		if($serverConfig->getJobType() == ServerConfig::JOB_TYPE_EXEC){
			$this->sendExec($mail);
		}else if($serverConfig->getJobType() == ServerConfig::JOB_TYPE_PHP){
			$this->sendPhp($mail);
		}

		CMSApplication::jump("Mail.SendBox?sended");
    	exit;
    }
	
	/**
	 * execでの実行処理
	 * @param Mail $mail
	 */
    function sendExec($mail){

		//二重送信エラー
    	if($mail->getStatus() >= Mail::STATUS_SENDING){
    		throw new Exception("二重送信の危険性あり");
    	}
    	//メール送信実行
    	if(strpos(PHP_OS,"WIN") !== false){

    		$php_path = "php.exe";
    		$job_path = dirname(SOY2::RootDir()) . "\\bin\\job.php";
    		$job_args = "-send=" . $mail->getId();
    		$cmdline = 'cmd.exe /C ' . $php_path . ' "' . $job_path . '" ' . $job_args;
    		$shell = new COM("WScript.Shell");
			$shell->Run($cmdline, 0, false);
			unset($shell);
			$shell = null;

    	}else{
    		$php_path = $this->serverConfig->getPhpPath();
    		$job_path = "\"" . dirname(SOY2::RootDir()) . "/bin/job.php\" -send=" . $mail->getId();

			$res = "";
    		exec($php_path . " " . $job_path . " 2>&1",$res);
    		
    		SOYMailLog::add("[Exec]",implode("\n",$res));
    	}
    }
	
	/**
	 * phpでの実行
	 * @param Mail $mail
	 */
    function sendPhp($mail){
		$argv = array(
			"-send=" . $mail->getId(),
			"-internal=1"
		);

		$argc = count($argv);

		$job_path = dirname(SOY2::RootDir()) . "/bin/job.php";
		include_once($job_path);
    }
}
?>