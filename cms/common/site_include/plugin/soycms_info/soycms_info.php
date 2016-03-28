<?php
/*
 * SOY CMS お知らせ表示プラグイン
 *
 */
SOYCMS_Info_Plugin::registerPlugin();

class SOYCMS_Info_Plugin{
	
	const PLUGIN_ID = "soycms_info";
	const INTERVAL = 86400;	//24*60*60
	const INFO_RSS_URL = "http://www.soycms.net/info/feed?feed=rss&soycms_info=1";
	const FORUM_RSS_URL = "http://www.soycms.org/rss.php?soycms_info=1";
	
	private $rssCache = array();
	
	const DEFAULT_ADMIN = 30;
	const SITE_ADMIN = 20;
	const ENTRY_ADMIN = 10;
	const DRAFT_ENTRY_ADMIN = 1;
	public $display_config_for_admin = array(
		self::DEFAULT_ADMIN => 1,
		self::SITE_ADMIN => 1,
		self::ENTRY_ADMIN => 1,
		self::DRAFT_ENTRY_ADMIN => 1,
	);
	
	function getId(){
		return SOYCMS_Info_Plugin::PLUGIN_ID;
	}
	
	/**
	 * 初期化
	 */
	function init(){
		
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY CMS更新情報プラグイン",
			"description"=>"SOY CMSの更新情報を表示します",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));	
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));
		
		if(
			   $this->display_config_for_admin[self::DEFAULT_ADMIN] && UserInfoUtil::isDefaultUser()
			|| $this->display_config_for_admin[self::SITE_ADMIN] && UserInfoUtil::hasSiteAdminRole() && ! UserInfoUtil::isDefaultUser()
			|| $this->display_config_for_admin[self::ENTRY_ADMIN] && UserInfoUtil::hasEntryPublisherRole() && ! UserInfoUtil::hasSiteAdminRole() && ! UserInfoUtil::isDefaultUser()
			|| $this->display_config_for_admin[self::DRAFT_ENTRY_ADMIN] && ! UserInfoUtil::hasEntryPublisherRole() && ! UserInfoUtil::hasSiteAdminRole() && ! UserInfoUtil::isDefaultUser()
		){
			CMSPlugin::addWidget($this->getId(),array(
				$this,"widget"
			));
		}	
	}
	
	function widget(){
		if(ini_get("allow_url_fopen")){
			list($rss,$time) = $this->loadRSS(self::INFO_RSS_URL);
			list($forum,$time2) = $this->loadRSS(self::FORUM_RSS_URL);
			
			$html = $this->getStyleSheet();
			if($rss)$html .= $this->outputRSS("お知らせ",$rss,$time);
			if($forum)$html .= $this->outputRSS("フォーラム",$forum,$time,2);
			
			if(!$rss && !$forum){
				$html = "<p>RSSの取得に失敗しました。</p>";
			}
		}else{
			$html = "allow_url_fopen=0の環境では情報が取得できません。";
		}
		
		return $html;
	}
	
	function outputRSS($title,$rss,$time,$count = 3){
		$items = @$rss->channel->item;
		
		$i = 0;
		
		$html = array();
		
		$html[] = "<span class=\"time\">最終取得: " . date("Y-m-d H:i:s",$time) . "</span>";
		$html[] = "<h1>" . $title . "</h1>";
		
		$html[] = "<ul>";
		
		if(count($items)>0){
			foreach($items as $item){
				if($i>=$count)break;
				
				$link = $item->link;
				$title = $item->title;
				
				$title = mb_strimwidth(SOY2HTML::ToText($title),0,80,"...");
				
				$html[] = '<li>';
				$html[] = '<a href="'.htmlspecialchars($link,ENT_QUOTES,"UTF-8").'" target="_blank;">' . htmlspecialchars($title,ENT_QUOTES,"UTF-8") . '</a>';
				$html[] = '</li>';
				
				$i++;
			}
		}
		$html[] = "</ul>";
		
		return implode("",$html);
	}
	
	/**
	 * 設定画面の表示
	 */
	function config_page($message){
		include(dirname(__FILE__)."/config.php");
		$form = SOY2HTMLFactory::createInstance("SOYCMSInfoConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	/**
	 * getStyleSheet
	 */
	function getStyleSheet(){
		
		$style = '<style type="text/css">';
		$style .= 'body{font-size:70%;color:gray;}';
		$style .= 'h1{font-size:80%;margin:0;padding:0;}';
		$style .= 'ul,li{margin:0;padding:0;list-style:none;}';
		$style .= 'ul{margin:5px 0;}';
		$style .= '.time{font-size:x-small;float:right;font-weight:normal;}';
		$style .= '</style>';
		
		return $style;
	}
	
	/**
	 * RSSの読み込み
	 */
	function loadRSS($url){
		
		$now = time();
		$xml = null;
		
		if(isset($this->rssCache[$url]) && (($this->rssCache[$url]["last_update_date"] + self::INTERVAL) > $now)){
			
			//キャッシュから
			return array(simplexml_load_string($this->rssCache[$url]["contents"]),$this->rssCache[$url]["last_update_date"]);
		}
		$ctx = stream_context_create(array( 
			'http' => array( 
				'timeout' => 5
			) 
		));
		 
		$contents = file_get_contents($url, false, $ctx);
		if(isset($contents)){
			$xml = simplexml_load_string($contents);
			
			$this->rssCache[$url] = array(
				"last_update_date" => $now,
				"contents" => $contents
			);
			
			CMSPlugin::savePluginConfig($this->getId(),$this);
		}
			
		
		return array($xml,$now);
		
	}
	
	/**
	 * キャッシュのクリア
	 */
	function clearCache(){
		$this->rssCache = array();
	}
	
	/**
	 * 管理者権限別表示設定の更新
	 */
	function updateDisplayConfig($config){
		$this->display_config_for_admin = $config;
	}
	
	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		
		//管理側のみ
		if(defined("_SITE_ROOT_"))return;
		
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_Info_Plugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_Info_Plugin();
			
			//この時プラグインを強制的に有効にする
			$filepath = CMSPlugin::getSiteDirectory().'/.plugin/'. SOYCMS_Info_Plugin::PLUGIN_ID;
			if(!file_exists($filepath . ".inited") && ini_get("allow_url_fopen")){
				@file_put_contents($filepath .".active","active");
				@file_put_contents($filepath .".inited","inited");
			}
		}
		CMSPlugin::addPlugin(SOYCMS_Info_Plugin::PLUGIN_ID,array($obj,"init"));
	}
	
}

?>