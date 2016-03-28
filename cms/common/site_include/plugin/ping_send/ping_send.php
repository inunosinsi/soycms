<?php
define('PING_SEND_PLUGIN',"ping_send");

//初期化
$obj = CMSPlugin::loadPluginConfig(PING_SEND_PLUGIN);
if(is_null($obj)){
	$obj = new PingSendPlugin();
}
CMSPlugin::addPlugin(PING_SEND_PLUGIN,array($obj,"init"));

class PingSendPlugin{
	
	//Ping送信先
	var $pingServers = array(
		"http://blogsearch.google.com/ping/RPC2",
		"http://api.my.yahoo.co.jp/RPC2",
		"http://rpc.technorati.com/rpc/ping",
		"http://blog.goo.ne.jp/XMLRPC",
		"http://rpc.reader.livedoor.com/ping",
		"http://ping.myblog.jp",
		"http://www.blogpeople.net/servlet/weblogUpdates",
		"http://ping.bloggers.jp/rpc/",
		"http://rpc.weblogs.com/RPC2",
		"http://ping.fc2.com",
		"http://ping.namaan.net/rpc/",
		"http://ping.rss.drecom.jp/",
		"http://ping.ask.jp/xmlrpc.m",
	);
	
	//最終送信時刻
	var $lastSendDate = array();
	
	
	function getId(){
		return PING_SEND_PLUGIN;
	}
	
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"更新Ping送信プラグイン",
			"description"=>"更新Pingを送信することが出来ます。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));	
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));
	}
	
	function config_page(){
		
		//Pingサーバの情報を取得
		if(isset($_POST["update_ping_server"])){
			$this->pingServers = explode("\n",$_POST["ping_server"]);
			$this->pingServers = array_unique($this->pingServers);
			CMSPlugin::savePluginConfig($this->getId(),$this);
			CMSPlugin::redirectConfigPage();
			exit;
		}
		
		//Ping送信
		if(isset($_POST["send_ping"])){
			$id = $_POST["blog_id"];
			
			$now = time();
			if(strlen($id) && is_numeric($id)){
				
				set_time_limit(600);
				
				$id = (int)$id;
				
				$blogDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
				
				$this->sendUpdatePings($id,$this->pingServers,$blogDAO);
				$this->lastSendDate[$id] = $now;
				CMSPlugin::savePluginConfig($this->getId(),$this);
			}
			
			$html = array();
			$html[] = "<html><head></head><body>";
			$html[] = "<script type=\"text/javascript\">";
			$html[] = "var ele = window.parent.document.getElementById('send_ping_button_$id');";
			$html[] = "if(ele){ ele.removeAttribute('disabled'); } ";
			$html[] = "var ele = window.parent.document.getElementById('loading_$id');";
			$html[] = "if(ele){ ele.style.visibility='hidden' } ";
			$html[] = "var ele = window.parent.document.getElementById('last_send_ping_$id');";
			$html[] = "if(ele){ ele.innerHTML = '".date("Y-m-d H:i:s",$now)."'; } ";
			$html[] = "</script>";
			$html[] = "</body></html>";
			
			
			echo implode("\n",$html);
			
			exit;
		}
		
		//全ブログページを取得
		$blogDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
		$blogs = $blogDAO->get();
		
		ob_start();
		include_once(dirname(__FILE__)."/config.php");
		$html = ob_get_contents();
		ob_clean();
		
		return $html;
		
	}
	
	function sendUpdatePings($id,$servers,$blogDAO){
		try{
			$blogPage = $blogDAO->getById($id);
		}catch(Exception $e){
			return;
		}
		
		$title = $blogPage->getTitle();
		if(strlen($blogPage->getUri()) >0){
			$url = UserInfoUtil::getSiteURL() . $blogPage->getUri() . "/";
		}else{
			$url = UserInfoUtil::getSiteURL();
		}
		
		foreach($servers as $key => $value){
			
			if(strlen($value)<1)continue;
			
			$urls = parse_url($value);
			$host = $urls["host"];
			$path = @$urls["path"];
			
			$this->sendUpdatePing($host,$path,$title,$url);
			
		}
		
	}
	
	function sendUpdatePing($host, $path, $title, $url){
		
		$sock = @fsockopen($host, 80, $errno, $errstr, 2.0);
		$result = "";
		if($sock){
			$title_str = htmlspecialchars($title);
			$content =
				   "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n" .
				   "<methodCall>\r\n" .
				   "<methodName>weblogUpdates.ping</methodName>\r\n" .
				   "<params>\r\n" .
				   "<param>\r\n" .
				   "<value>$title_str</value>\r\n" .
				   "</param>\r\n" .
				   "<param>\r\n" .
				   "<value>$url</value>\r\n" .
				   "</param>\r\n" .
				   "</params>\r\n" .
				   "</methodCall>\r\n";
			$length = strlen($content);
			$req = "POST $path HTTP/1.0\r\n" .
				   "Host: $host\r\n" .
				   "Content-Type: text/xml\r\n" .
				   "Content-Length: $length\r\n" .
				   "\r\n" . $content;
				   
			fputs($sock, $req);
			while(!feof($sock)){
				$result .= fread($sock, 1024);
			}
		}
		return $result;
	}
}
?>
