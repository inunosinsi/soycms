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
				
			}

			CMSApplication::jump();
		}


	}

    function IndexPage() {
    	WebPage::WebPage();
    	
    	//メール情報出力
    	$this->outputMailInfo();
    	
    	//ユーザ情報出力
    	$this->outputUserInfo();

    	//ジョブ情報出力
    	$this->outputJobInfo();
    }
    
    function outputMailInfo(){
    	$mailDAO = SOY2DAOFactory::create("MailDAO");
    	
    	$this->createAdd("unsend_mail","HTMLLabel",array(
    		"text" => $mailDAO->countSendMail()
    	));
    	
    	$errorMailDAO = SOY2DAOFactory::create("ErrorMailDAO");
    	
    	$this->createAdd("error_mail","HTMLLabel",array(
    		"text" => $errorMailDAO->countErrorMail()
    	));
    }
    
    function outputUserInfo(){
    	$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
    	
    	$this->createAdd("user_count","HTMLLabel",array(
    		"text" => $extendLogic->countUser()
    	));
    }

    function outputJobInfo(){
    	$config = SOY2DAOFactory::create("ServerConfigDAO")->get();

    	$this->createAdd("job_toggle_button","HTMLLabel",array(
    		"name" => "job_toggle_button",
    		"value" => ($config->getJobIsActived()) ? "無効にする" : "有効にする"
    	));

    	$this->createAdd("job_is_active","HTMLLabel",array(
    		"text" => $config->getJobStatusText()
    	));

    	$this->createAdd("job_last_execute_time","HTMLLabel",array(
    		"text" => ($config->getJobLastExecuteTime()) ? date("Y/m/d H:i",$config->getJobLastExecuteTime()) : "----/--/-- --:--"
    	));

    	$this->createAdd("job_next_execute_time","HTMLLabel",array(
    		"text" => ($config->getJobNextExecuteTime()) ? date("Y/m/d H:i",$config->getJobNextExecuteTime()) : "----/--/-- --:--"
    	));

		$this->createAdd("error","HTMLModel",array(
			"visible" => ($config->getJobNextExecuteTime() && $config->getJobNextExecuteTime() < time() && $config->getJobIsActived() != 0)
		));

    }
}
?>