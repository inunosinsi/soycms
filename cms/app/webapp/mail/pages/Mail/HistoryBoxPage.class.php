<?php

class HistoryBoxPage extends WebPage{

    function __construct($args) {
    	parent::__construct();

    	//メール表示件数
    	$limit = 15;
    	$startPage = (isset($args[0])) ? (int)$args[0] : 1;
    	$offset = ($startPage - 1) * $limit;

    	//DAO
    	SOY2DAOConfig::setOption("limit_query",true);
    	$mailDAO = SOY2DAOFactory::create("MailDAO");

    	//合計件数取得
    	$total = $mailDAO->countSendedMail();

    	//メール取得
    	$mailDAO->setOrder("sended_date desc");
    	$mailDAO->setLimit($limit);
    	$mailDAO->setOffset($offset);
    	try{
    		$mails = $mailDAO->getSendedMail();
    	}catch(Exception $e){
    		$mails = array();
    	}
    	
    	//メール一覧
    	$this->createAdd("mail_list","_common.HistoryBoxMailListComponent",array(
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
    	$pageURL = SOY2PageController::createLink("mail.Mail.HistoryBox");
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
    }
}
?>