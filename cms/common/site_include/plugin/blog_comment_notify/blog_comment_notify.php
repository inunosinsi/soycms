<?php
/*
 * Created on 2013/9/24
 * コメントがあったらメール送信
 */
SOYCMS_BlogCommnetNotifyPlugin::registerPlugin();

class SOYCMS_BlogCommnetNotifyPlugin{
	
	const PLUGIN_ID = "blog_comment_notify";
	
	/**
	 * @return array( <integer>ブログID => array(
	 * 	"flg", //有効フラグ
	 * 	"mail_to", //送り先 改行ごとに複数入力
	 * 	"mail_title", //メール タイトル
	 * 	"mail_content", //メール 本文
	 * 	));
	 */
	private $blogConfig = array();
	
	private $mailAddress = array();
	private $commentMail = array(
		"title" => "【通知】コメント投稿のお知らせ",
		"header" => "コメントが投稿されました",
		"isSend" => true
	);
	
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ブログコメント メール通知プラグイン",
			"description"=>"ブログのコメント投稿時にメール送信",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"dev"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
			
			//設定画面
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this, "config_page"
			));
			
			if(isset($_GET["comment"])){
				CMSPlugin::setEvent("afterSubmitComment",$this->getId(),array($this,"afterSubmitComment"));
			}
		}
	}
	
	function getId(){
		return SOYCMS_BlogCommnetNotifyPlugin::PLUGIN_ID;
	}
	
	/**
	 * コメント投稿後の動作
	 */
	function afterSubmitComment($args){
		
		$comment = $args["entryComment"];
		$page = $args["page"];
		$configs = $this->getBlogConfig();
		
		if(!isset($configs[$page->id]))return;//設定があればメール送信

		$config = $configs[$page->id];
			
		$title = $config["mail_title"];
		$content = $config["mail_content"];
		$to_list = $config["mail_to"];
		$to_list = explode("\n", $to_list);
		
		//置換処理
		$title = $this->replaceText($title, $comment, $page);
		$content = $this->replaceText($content, $comment, $page);
		
		$logic = SOY2LogicContainer::get("logic.site.MailConfig.MailLogic");
		foreach($to_list as $to){
			$to = trim($to);
			$logic->sendMail($to, $title, $content);
		}
		 
		
	}
	
	/**
	 * @param string $text 置換対象 
	 * @param array $comment
	 * @param CMSBlogPage $CMSBlog
	 * @return string 置換後のテキスト
	 */
	function replaceText($text, $comment, $CMSBlog){
		$list = array();
		$page = $CMSBlog->page;
		$entry = $CMSBlog->entry;
		
		//[BLOG_TITLE] ブログ名
		$list["[BLOG_TITLE]"] = $page->getTitle();
		
		//[ENTRY_TITLE] 記事名
		$list["[ENTRY_TITLE]"] = $entry->getTitle();
		
		//[ENTRY_URL] 記事URL
		$link = CMSPageController::createRelativeLink(substr($_SERVER["REQUEST_URI"],0,strpos($_SERVER["REQUEST_URI"],"?")),true);
		$list["[ENTRY_URL]"] = $link;
		
		//[ENTRY_DATE] 記事投稿日時
		$list["[COMMENT_DATE]"] = date("Y年m月d日 H時i分s秒", $entry->getCdate());
		
		//[COMMENT_TITLE] コメントタイトル
		$list["[COMMENT_TITLE]"] = $comment->getTitle();
		
		//[COMMENT_BODY] コメント本文
		$list ["[COMMENT_BODY]"] = $comment->getBody();
		
		//[COMMENT_AUTHOR] コメント投稿者
		$list["[COMMENT_AUTHOR]"] = $comment->getAuthor();
		
		//[COMMENT_DATE] コメント投稿日時
		$list["[COMMENT_DATE]"] = date("Y年m月d日 H時i分s秒", $comment->getSubmitDate());
		
		foreach($list as $search => $replace){
			$text = str_replace($search, $replace, $text);
		}
		
		return $text;
	}
	
	
	
	/**
	 * プラグイン管理画面 設定
	 */
	function config_page(){
		include_once(dirname(__FILE__). "/config/config_form.php");
		$page = SOY2HTMLFactory::createInstance("config_form", array(
			"pluginObj" => $this
		));
		
		$page->execute();
		$html = $page->getObject(); 

		return $html;
	}
	
	function getMailAddress() {
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress) {
		if(is_string($mailAddress)){
			$mailAddress = explode("\n", $mailAddress);
		}
		$this->mailAddress = $mailAddress;
	}
	function getCommentMail() {
		return $this->commentMail;
	}
	function setCommentMail($commentMail) {
		$this->commentMail = $commentMail;
	}

	public function getBlogConfig() {
		return $this->blogConfig;
	}
	public function setBlogConfig($blogConfig) {
		$this->blogConfig = $blogConfig;
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_BlogCommnetNotifyPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_BlogCommnetNotifyPlugin();
		}
		
		CMSPlugin::addPlugin(SOYCMS_BlogCommnetNotifyPlugin::PLUGIN_ID,array($obj,"init"));
	}
	
}
?>
