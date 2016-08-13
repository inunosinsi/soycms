<?php

class SendBoxPage extends WebPage{
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

			CMSApplication::jump("Mail.SendBox");
		}


	}

    function __construct() {
    	WebPage::__construct();
    	
    	$this->createAdd("sended_message","HTMLModel",array(
			"visible" => (isset($_GET["sended"]))
    	));
    	
    	//メール表示件数
    	$limit = 15;
    	$startPage = (isset($args[0])) ? (int)$args[0] : 1;
    	$offset = ($startPage - 1) * $limit;
    	
    	//DAO
    	SOY2DAOConfig::setOption("limit_query",true);
    	$mailDAO = SOY2DAOFactory::create("MailDAO");

    	$mailDAO->setOrder("update_date desc");

    		
    	//合計件数取得
    	$total = $mailDAO->countSendMail();
    	
    	//メール取得
    	$mailDAO->setLimit($limit);
    	$mailDAO->setOffset($offset);
    	$mails = $mailDAO->getSendMail();
    	
    	$this->createAdd("mail_list","_common.SendBoxMailListComponent",array(
    		"list" => $mails
    	));
    	
    	//件数情報表示
    	$start = $offset;
    	$end = $start + count($mails);
    	if($end > 0 && $start == 0)$start = 1;
    	    	
    	$this->createAdd("count_start","HTMLLabel",array(
    		"text" => $start
    	));
    	
    	$this->createAdd("count_end","HTMLLabel",array(
    		"text" => $end
    	));
    	
    	$this->createAdd("count_max","HTMLLabel",array(
    		"text" => $total
    	));
    	
    	//ページャー作成
    	$pageURL = SOY2PageController::createLink("mail.Mail.SendBox");
    	$this->createAdd("next_pager","HTMLLink",array(
    		"link" => ($total > $end) ? $pageURL . "/" . ($startPage + 1) : $pageURL . "/" . $startPage,
    		"class" => ($total <= $end) ? "pager_disable" : ""
    	));
    	$this->createAdd("prev_pager","HTMLLink",array(
    		"link" => ($startPage > 1) ? $pageURL . "/" . ($startPage - 1) : $pageURL . "/" . ($startPage),
    		"class" => ($startPage <= 1) ? "pager_disable" : ""
    	));
    	$pagers = range(
    		max(1, $startPage - 3),
    		min((int)($total / $limit) + 1, $startPage + 3)
    	);
    	
    	$this->createAdd("pager_list","_common.SimplePagerComponent",array(
    		"url" => $pageURL,
    		"current" => $startPage,
    		"list" => $pagers
    	));
    	
    	$this->outputJobInfo();
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