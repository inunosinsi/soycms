<?php
/*
 * 簡易コメントスパム対策プラグイン
 * 
 * Created on 2008/12/15
 */
SOYCMS_SpamBlockerPlugin::registerPlugin();


class SOYCMS_SpamBlockerPlugin{
	
	const PLUGIN_ID = "soycms_spam_blocker";
	
	private $useKeyword = false;
	private $prohibitionWords = array();
	private $name = "keyword";
	private $keyword = "確認";
	
	function getId(){
		return SOYCMS_SpamBlockerPlugin::PLUGIN_ID;
	}
	
	/**
	 * 初期化
	 */
	function init(){
		
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"コメントスパム対策プラグイン",
			"description"=>"ブログのコメントのスパムを対策します",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.0.1"
		));	
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));
		
		//イベント登録
		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::setEvent('onSubmitComment',$this->getId(),array($this,"onSubmitComment"));
		}
	}
	
	/**
	 * 設定画面
	 */
	function config_page(){
		
		if(isset($_POST["save"])){
			$this->useKeyword = (boolean)$_POST["useKeyword"];
			$this->keyword = $_POST["keyword"];
			$this->name = $_POST["name"];
			$this->prohibitionWords = explode("\n",$_POST["prohibitionWords"]);
			$this->prohibitionWords = array_map("trim",$this->prohibitionWords);  
			
			CMSPlugin::savePluginConfig($this->getId(),$this);
			CMSPlugin::redirectConfigPage();
			
			exit;
		}
		
		
		ob_start();
		include_once(dirname(__FILE__) . "/config.php");
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * コメント投稿
	 */
	function onSubmitComment($args){
		$comment = @$args["entryComment"];
		
		if($this->useKeyword){
			if(!isset($_POST[$this->name]))return false;
			if($_POST[$this->name] != $this->keyword)return false;	
		}
		
		$before = $comment->getBody();
		$after = str_replace($this->prohibitionWords,"",$before);
		
		if($before != $after){
			return false;
		}
	}
	
	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_SpamBlockerPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_SpamBlockerPlugin();
		}
		CMSPlugin::addPlugin(SOYCMS_SpamBlockerPlugin::PLUGIN_ID,array($obj,"init"));
	}
	
}
?>
