<?php

PingNewPlugin::register();
class PingNewPlugin{

	const PLUGIN_ID = "ping_new";

	//Ping送信先
	var $pingServers;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"更新Ping送信プラグイン２",
			"description"=>"更新Pingを送信することが出来ます。",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//管理側であれば、徐々にファイルを指定のディレクトリに移行する
			if(!defined("_SITE_ROOT_")){
				//何もしない
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));

			//公開側
			}else{
				//何もしない
			}
		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	function onEntryUpdate($arg){
		if(!strlen(trim($this->pingServers))) return;

		//記事を公開にした時にPINGを送信する
		$entry = $arg["entry"];
		if($entry->getIsPublished() != Entry::ENTRY_ACTIVE) return;

		//公開期限外の場合も処理しない
		$now = time();
		if($entry->getOpenPeriodStart() > $now || $entry->getOpenPeriodEnd() < $now) return;

		if(!isset($_POST["label"]) || !is_array($_POST["label"]) || !count($_POST["label"])) return;

		//既に送信済みか？
		SOY2::import("site_include.plugin.ping_new.util.PingUtil");
		if(PingUtil::checkSended($entry->getId())) return;

		//ブログに関するラベルがあるか？
		list($title, $url) = self::_getBlogInfo($_POST["label"]);
		if(!strlen($url)) return;

		$xml = self::_buildXml($title, $url);
		$headers = array(
			'Content-Type: application/xml',
			'Content-Length: '.strlen($xml)
		);

		$cxt = stream_context_create(
			array(
				'http'=>array(
					'method'=>'POST',
					'header'=>implode("\r\n", $headers),
					'content'=>$xml
				)
			)
		);

		$isSuccess = true;
		foreach(explode("\n", $this->pingServers) as $server){
			$server = trim($server);
			if(!strlen($server)) continue;
			if(!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $server)) continue;

			$resp = @file_get_contents($server, false, $cxt);
			if(is_bool($resp) && !$resp) $isSuccess = false;
			if(!strpos($resp, "Thanks for the ping")) $isSuccess = false;
		}

		// 一度投稿したら、二回目の投稿を行わないようにしたい
		if($isSuccess) PingUtil::save($entry->getId());
	}

	private function _getBlogInfo($labels){
		try{
			$blogs = SOY2DAOFactory::create("cms.BlogPageDAO")->get();
		}catch(Exception $e){
			$blogs = array();
		}
		if(!count($blogs)) return array("", "");

		$title = "";	//ブログのタイトルとURL
		$url = "";
		foreach($blogs as $blog){
			if(is_numeric(array_search($blog->getBlogLabelId(), $labels))){
				$title = $blog->getTitle();
				$uri = ltrim($blog->getUri() . "/" . $blog->getTopPageUri(), "/");
				$url = rtrim(UserInfoUtil::getSitePublishURL(), "/") . "/"  . $uri;
				break;
			}
		}

		return array($title, $url);
	}


	private function _buildXml($title, $url){
		$html = array();
		$html[] = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
		$html[] = "<methodCall>";
		$html[] = "<methodName>weblogUpdates.ping</methodName>";
		$html[] = "<params>";
		$html[]	= "<param>";
		$html[] = "<value>" . htmlspecialchars($title) . "</value>";
		$html[] = "</param>";
		$html[] = "<param>";
		$html[] = "<value>".$url."</value>";
		$html[] = "</param>";
		$html[] = "</params>";
		$html[] = "</methodCall>";
		return implode("\r\n", $html);
	}

	function config_page(){
		SOY2::import("site_include.plugin.ping_new.config.PingConfigPage");
		$form = SOY2HTMLFactory::createInstance("PingConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM Ping", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/CREATE/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = trim("CREATE" . $sql);
			try{
				$dao->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	function getPingServers(){
		return $this->pingServers;
	}
	function setPingServers($pingServers){
		$this->pingServers = $pingServers;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new PingNewPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
