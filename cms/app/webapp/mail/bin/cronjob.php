<?php

/*
 * crontabで/root/インストールディレクトリ(cms)/app/webapp/mail/bin/cronjob.phpを指定するとメール配信を実行します。
 */

//カレントディレクトリを設定する
chdir(dirname(__FILE__));

//timeout時間を設定する
set_time_limit(0);

//関数の読み込み
$files = scandir(dirname(__FILE__));
foreach($files as $file){
	if(preg_match('/^function\..*\.php$/',$file)){
		include_once($file);
	}
}

//設定ファイルの読み込み
if(false == defined("CMS_COMMON")){
	define("CMS_COMMON", dirname(dirname(dirname(dirname(dirname(__FILE__))))). "/common/");
	include_once(CMS_COMMON . "lib/soy2_build.php");
	include_once(CMS_COMMON . "soycms.config.php");
	include_once(dirname(dirname(__FILE__)) . "/config.php");
}

//エラー表示について
error_reporting(E_ALL);
ini_set("display_errors","On");

/**
 * @ToDo 予約中のメールを取得する
 */
$cronLogic = SOY2Logic::createInstance("logic.job.CronLogic");
$reservations = $cronLogic->getMailReservations();

$count = count($reservations);

//送信予定のメールが存在しない場合はログを出力しない
if($count > 0){
	register_shutdown_function("soymail_log");
	ob_start();
	
	$counter = 1;
	
	foreach($reservations as $reservation){
		if($counter === 1) echo "[START]Execute by cron\n";
		
		//ジョブの実行
		echo "mailID:" . $reservation["mailId"] . "\n";
	
		try{
			echo "start sending mail....\n";
			sendmail($reservation["mailId"], $reservation["offset"]);
			echo ".....finish!\n";
			
			//予約テーブルの値を送信済みにする
			$cronLogic->update($reservation["mailId"]);
		}catch(Exception $e){	
			echo "[FAILED]failed to sending mail(".$e->getMessage().")\n";
			echo "------------------------------------------------------\n";
			echo var_export($e);
		}
		
		if($counter !== $count) echo "\n\n";
		$counter++;
	}
}


?>