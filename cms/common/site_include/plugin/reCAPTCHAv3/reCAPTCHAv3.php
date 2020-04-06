<?php

reCAPTCHAv3Plugin::register();

class reCAPTCHAv3Plugin{

	const PLUGIN_ID = "re_captcha_v3";

	private $siteKey;
	private $secretKey;

	//挿入しないページ
	//Array<ページID => 0 | 1> 挿入しないページが1
	var $config_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $config_per_blog = array();

	var $ssl_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $ssl_per_blog = array();


	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"Google reCAPTCHA v3",
			"description"=>"GoogleのreCAPTCHA v3を使用する",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//管理画面側
			if(!defined("_SITE_ROOT_")){

			//公開側
			}else{
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];
		if(!strlen($this->siteKey) || !strlen($this->secretKey)) return $html;
		$page = &$arg["page"];
		if(strpos($page->getUri(), ".xml") !== false) return $html;

		$page = $arg["page"];
		$webPage = $arg["webPage"];

		//表示しない設定なら挿入しない
		if(!isset($this->config_per_page[$page->getId()]) || $this->config_per_page[$page->getId()] != 1){
			return $html;
		}else if($page->getPageType() == Page::PAGE_TYPE_BLOG){
			if(!isset($this->config_per_blog[$page->getId()][$webPage->mode]) || $this->config_per_blog[$page->getId()][$webPage->mode] != 1){
				return $html;
			}
		}


		$js = array();

		//取り急ぎお問い合わせフォームのみ
		if($page->getPageType() == Page::PAGE_TYPE_APPLICATION && $page->getPageConfigObject()->applicationId == "inquiry"){
			$js[] = "<script src=\"https://www.google.com/recaptcha/api.js?render=" . $this->siteKey . "\"></script>";
			$js[] = "<script>";
			$code = file_get_contents(dirname(__FILE__) . "/js/script.js");
			$js[] = str_replace("##SITE_KEY##", $this->siteKey, $code);
			$js[] = "</script>";
		}else{	//お問い合わせフォーム以外
			$js[] = "<script>";
			$code = file_get_contents(dirname(__FILE__) . "/js/lazy_script.js");
			$js[] = str_replace("##SITE_KEY##", $this->siteKey, $code);
			$js[] = "</script>";
		}

		$script = implode("\n", $js);

		// if(stripos($html,'</body>') !== false){
		// 	return str_ireplace('</body>',$script."\n".'</body>',$html);
		// }else if(stripos($html,'</html>') !== false){
		// 	return str_ireplace('</html>',$script."\n".'</html>',$html);
		// }else{
			return $html.$script;	//強制的に末尾に入れてみる
		//}
	}

	function config_page($mes){
		SOY2::import("site_include.plugin.reCAPTCHAv3.config.reCAPTCHAConfigPage");
		$form = SOY2HTMLFactory::createInstance("reCAPTCHAConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getSiteKey(){
		return $this->siteKey;
	}
	function setSiteKey($siteKey){
		$this->siteKey = $siteKey;
	}

	function getSecretKey(){
		return $this->secretKey;
	}
	function setSecretKey($secretKey){
		$this->secretKey = $secretKey;
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new reCAPTCHAv3Plugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
