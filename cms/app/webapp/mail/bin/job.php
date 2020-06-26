<?php
/*
 * SOY Mail内でのメール配信処理。このjob.phpを叩かれることでメールを送信する
 * 
 * 使い方
 * 引数
 * -send
 * -send=<送信するメールID>
 * メールを送信する
 *
 * -receive
 * エラーメールを受信する
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

//引数無い時は使い方を表示して終了
if($argc < 0){
	print_usage();
	exit;
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

//定期実行か
$isInterval = false;
$isInternal = false; //php実行

//送信ジョブを起動するかどうか
$sendJob = false;
$sendMailId = null;

//受信ジョブを起動するかどうか
$receiveJob = false;

for($i=0,$l=$argc;$i<$l;$i++){
	$arg = $argv[$i];

	if(preg_match('/-send=?([^\s]*)?/',$arg,$tmp)){
		$sendJob = true;
		if(isset($tmp[1])){
			$sendMailId = $tmp[1];
		}
	}

	if(preg_match('/-receive/',$arg)){
		$receiveJob = true;
	}

	if(preg_match('/-job/',$arg	)){
		$sendJob = true;
		$receiveJob = true;
		$isInterval = true;
		break;
	}
	
	if(preg_match('/-internal/',$arg	)){
		$isInternal = true;
		break;
	}
}

register_shutdown_function("soymail_log");
ob_start();

if($isInterval){
	try{
		echo "[START]Execute By Job \n";
		
		//ジョブの開始時刻を記録
		$dao = SOY2DAOFactory::create("ServerConfigDAO");
		$dao->begin();
		$config = $dao->get();

		//無効に指定されている時
		if($config->getJobIsActived() == 0){
			exit;
		}
		//次回起動時刻が現在の時間より先のとき中止。
		//10:12:30に登録しても10:12:00に起動される。から６０秒足して補正
		if($config->getJobNextExecuteTime() > time()+60){
			echo "[END]next job is ." . date("Y-m-d H:i:s",$config->getJobNextExecuteTime());
			exit;
		}
		//実行中なら終了
		if($config->getJobIsActived() == -1){
			echo "[END]job is runnnig.";
			exit;
		}
		//実行中にする
		$config->setJobIsActived(-1);

		$config->setJobLastExecuteTime(time());
		$config->setJobNextExecuteTime(null);

		$dao->update($config);
		$dao->commit();
	}catch(Exception $e){
		
		echo "[FAILED]Failed to update job(".$e->getMessage().")";
		
		$dao->rollback();
		exit;
	}
}else if($isInternal == false){
	echo "[START]Execute by exec. \n";	
}else{
	echo "[START]Execute by php. \n";
}

//ジョブの実行
try{
	if($receiveJob){
		//receivemail();
	}
	if($sendJob){
		echo "start sending mail....\n";
		sendmail($sendMailId);
		echo ".....finish!\n";
	}
}catch(Exception $e){
	$dao = SOY2DAOFactory::create("ServerConfigDAO");
	$dao->setJobIsActived(0);
	$dao->setJobNextExecuteTime(null);
	
	echo "[FAILED]failed to sending mail(".$e->getMessage().")\n";
	echo "------------------------------------------------------\n";
	echo var_export($e);
	exit;

}

//次回ジョブの登録
if($isInterval){
	try{
		$dao = SOY2DAOFactory::create("ServerConfigDAO");

		$config = $dao->get();

		//有効(もしくは実行中の場合)
		if($config->getJobIsActived() !== 0){
			$config->setJobIsActived(1);
			$config->setJobLastExecuteTime(time());
			$logic = SOY2Logic::createInstance("logic.job.JobLogic");
			$next = $logic->registNextJob();

			if($next){
				$config->setJobNextExecuteTime($next);
			}else{
				$config->setJobNextExecuteTime(0);
			}
		}

		$dao->update($config);
	}catch(Exception $e){

	}
}

?>
