<?php

class SendTrackbackAction extends SOY2Action{

	private $id;
	private $pageId;

    function execute($response,$form,$request) {
    	
    	//TODO セーフモードの場合はタイムアウトが設定できないけどどうしようか
    	@set_time_limit(0);
    	
    	try{
	    	$dao = SOY2DAOFactory::create("cms.EntryDAO");
	    	$entry = $dao->getById($this->id);
	    	$pageDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
	    	$page = $pageDAO->getById($this->pageId);
	    	
	    	//記事のURLの作成 送信先のDBの長さ対策用に、確実に届くであろうIDで。
	    	$url = UserInfoUtil::getSiteURL();
	    	if(strlen($page->getUri())) $url .= $page->getUri().'/';
			$url .= $page->getEntryPageUri().'/' .	$entry->getId();//rawurlencode($entry->getAlias());
			
	    	foreach($form->trackback as $trackbackURL){
	    		if(trim($trackbackURL) == ""){
	    			continue;
	    		}
	    		$this->SendTrackback($trackbackURL,$page->getTitle(),$entry->getTitle(),mb_strcut(strip_tags($entry->getContent()),0,252),$url);
	    	}
	    	
	    	return SOY2Action::SUCCESS;
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	
    }
    
	/**
	 * トラックバックを送る
	 * @param   String $server  送信先サーバのURL
	 * @param   String $name    ブログ名／サイト名
	 * @param   String $title   記事タイトル
	 * @param   String $excerpt 記事概要
	 * @param   String $url     記事URL
     */
	function SendTrackback($server, $name, $title, $excerpt, $url) {
	    //送信先サーバURLをホスト名とパス名に分解する
	    $arr = parse_url($server);
	    
	    $host = $arr["host"];       //ホスト名
	    $path = $arr["path"];       //パス名
	    $port = @$arr["port"];		//ポート
	    $query = @$arr['query'];
	    if(strlen($port)<1) $port = 80;
	    if(strlen($host)<1)    return (-1);
		
		//送信先サーバをオープンする
	    $sock = fsockopen($host, $port, $errno, $errstr, 60);
	    
	    if ($sock == FALSE)     return (-1);
		
	    //トラックバック文字列をつくる
	    $str =  "title=" . rawurlencode($title);
	    $str .= "&url="  . rawurlencode($url);
	    $str .= "&blog_name=" . rawurlencode($name);
	    $str .= "&excerpt=" . rawurlencode($excerpt);
	    
		//エンティティボディ
	    $request_path = (strlen($query)>0) ? "$path?$query" : $path ;
	    $request_host = ($port != "80") ? "$host:$port" : $host ;
	    
	    $body = "";
    	$body .= "POST $request_path HTTP/1.1\r\n";
    	$body .= "Host: $request_host\r\n";
		$body .= "Content-type: application/x-www-form-urlencoded\r\n";
	    $body .= "Content-length: " . strlen($str) . "\r\n";
	    $body .= "\r\n";
	    $body .= $str;
	    
		fputs($sock, $body);
		
		//fread
		$buf = "";
		while (!feof($sock)){
			$buf = $buf . fgets($sock,128);
		}
	
		//ソケットがタイムアウトしたかどうか調べる
		$stat = socket_get_status($sock);
		if($stat["timed_out"]){
			return -1;
		}
		
		//飛ばしっぱなしで終わり
	    fclose($sock);
	    return 1;
	}

	function setId($id) {
		$this->id = $id;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
	
	
}

class SendTrackbackActionForm extends SOY2ActionForm{
	var $trackback;
	
	function setTrackback($trackback){
		if(strlen(trim($trackback)) == 0){
			$this->trackback = array();
		}else{
			$order   = array("\r\n", "\n", "\r");
			$trackback = str_replace($order, '##LINE_BREAK##', $trackback);
			$this->trackback = explode("##LINE_BREAK##",$trackback);
			$this->trackback = array_diff($this->trackback,array(""));
		}
		
		
	    
	}
}
?>