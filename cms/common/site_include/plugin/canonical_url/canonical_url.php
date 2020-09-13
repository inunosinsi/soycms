<?php

class CanonicalUrlPlugin{

	const PLUGIN_ID = "canonical_url";

	private $isTrailingSlash = 1;	//URLの末尾にスラッシュを付けるか？
	private $isWww = 1;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){

		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"カノニカルURL挿入プラグイン",
			"description"=>"テンプレートに&lt;/head&gt;タグがある場合はカノニカルURLタグを自動で挿入します。",
			"author"=>"齋藤 毅",
			"url"=>"https://saitodev.co/",
			"mail"=>"info@saitodev.co",
			"version"=>"0.5"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//公開画面側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID,array($this,"onOutput"),array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];

		//ダイナミック編集では挿入しない
		if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE) return $html;

		//URLの末尾が.xmlか.jsonの場合は挿入しない
		if(strpos($_SERVER["REQUEST_URI"], ".xml") || strpos($_SERVER["REQUEST_URI"], ".json")) return $html;

		//RSSでは挿入しない
		if(strpos($html, '<rss version="2.0">') !== false || strpos($html, '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">') !== false) return null;

		//</head>が無い場合は挿入しない
		if(stripos($html, "</head>") === false) return $html;

		//既にCMSBlogPage.class.phpやCMSPage.class.phpでカノニカルURLを組み立てているので、パラメータなしでURLを呼び出せる
		$canonicalUrl = SOY2Logic::createInstance("logic.site.Page.PageLogic")->buildCanonicalUrl();

		//末尾が拡張子ではない場合
		preg_match('/.+\.(html|htm|php?)/i', $canonicalUrl, $tmp);
		if(!count($tmp) && (int)$this->isTrailingSlash === 1){
			$canonicalUrl = rtrim(trim($canonicalUrl), "/") . "/";
		}

		//wwwなし設定
		if((int)$this->isWww === 0){
			preg_match('/^https?:\/\/www\./', $canonicalUrl, $tmp);
			if(isset($tmp[0])){
				$canonicalUrl = str_replace("//www.", "//", $canonicalUrl);
			}
		}

		$tag = "<link rel=\"canonical\" href=\"" . $canonicalUrl . "\">";

		return str_ireplace('</head>', $tag."\n".'</head>', $html);
	}

	function config_page($message){
		SOY2::import("site_include.plugin.canonical_url.config.CanonicalUrlConfigPage");
		$form = SOY2HTMLFactory::createInstance("CanonicalUrlConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getIsTrailingSlash(){
		return $this->isTrailingSlash;
	}
	function setIsTrailingSlash($isTrailingSlash){
		$this->isTrailingSlash = $isTrailingSlash;
	}

	function getIsWww(){
		return $this->isWww;
	}
	function setIsWww($isWww){
		$this->isWww = $isWww;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new CanonicalUrlPlugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

CanonicalUrlPlugin::register();
