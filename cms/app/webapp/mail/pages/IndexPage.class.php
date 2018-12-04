<?php

class IndexPage extends WebPage{

	function doPost(){

		if(isset($_POST["job_toggle_button"])){

			try{
				$dao = SOY2DAOFactory::create("ServerConfigDAO");
				$config = $dao->get();

				//無効だった時は一回atを走らせる
				if($config->getJobIsActived() == 0){
					$logic = SOY2Logic::createInstance("logic.job.JobLogic");
					$next = $logic->registNextJob();

					if($next){
						$config->setJobNextExecuteTime($next);
						$config->setJobIsActived(1);
					}

				}else{
					//ジョブをクリア、というものは無い
					$config->setJobNextExecuteTime(null);
					$config->setJobIsActived(0);
				}
				$dao->update($config);
			}catch(Exception $e){
				//
			}

			CMSApplication::jump();
		}
	}

    function __construct() {
    	parent::__construct();

		//データベースの更新を調べる
		$checkVer = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic")->checkVersion();
		DisplayPlugin::toggle("has_db_update", $checkVer);

		//データベースの更新終了時に表示する
		$doUpdated = (isset($_GET["update"]) && $_GET["update"] == "finish");
		DisplayPlugin::toggle("do_db_update", $doUpdated);

		//上記二つのsoy:displayの表示用
		DisplayPlugin::toggle("do_update", ($checkVer || $doUpdated));

    	//メール情報出力
    	self::outputMailInfo();

    	//ユーザ情報出力
    	self::outputUserInfo();

    	//ジョブ情報出力
    	self::outputJobInfo();
    }

    private function outputMailInfo(){
    	$this->addLabel("unsend_mail", array(
    		"text" => SOY2DAOFactory::create("MailDAO")->countSendMail()
    	));

    	$this->addLabel("error_mail", array(
    		"text" => SOY2DAOFactory::create("ErrorMailDAO")->countErrorMail()
    	));
    }

    private function outputUserInfo(){
    	$this->createAdd("user_count","HTMLLabel",array(
    		"text" => SOY2Logic::createInstance("logic.user.ExtendUserDAO")->countUser()
    	));
    }

    private function outputJobInfo(){
    	$config = SOY2DAOFactory::create("ServerConfigDAO")->get();

    	$this->addLabel("job_toggle_button", array(
    		"name" => "job_toggle_button",
    		"value" => ($config->getJobIsActived()) ? "無効にする" : "有効にする"
    	));

    	$this->addLabel("job_is_active", array(
    		"text" => $config->getJobStatusText()
    	));

    	$this->addLabel("job_last_execute_time", array(
    		"text" => ($config->getJobLastExecuteTime()) ? date("Y/m/d H:i",$config->getJobLastExecuteTime()) : "----/--/-- --:--"
    	));

    	$this->addLabel("job_next_execute_time", array(
    		"text" => ($config->getJobNextExecuteTime()) ? date("Y/m/d H:i",$config->getJobNextExecuteTime()) : "----/--/-- --:--"
    	));

		$this->addModel("error", array(
			"visible" => ($config->getJobNextExecuteTime() && $config->getJobNextExecuteTime() < time() && $config->getJobIsActived() != 0)
		));
    }
}
