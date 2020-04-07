<?php

reCAPTCHAv3Plugin::register();

class reCAPTCHAv3Plugin{

	const PLUGIN_ID = "re_captcha_v3";

	private $siteKey;
	private $secretKey;

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
			"version"=>"0.5"
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

		if($page->getPageType() != Page::PAGE_TYPE_APPLICATION || $page->getPageConfigObject()->applicationId != "inquiry") return $html;

		$js = array();
		$js[] = "<script src=\"https://www.google.com/recaptcha/api.js?render=" . $this->siteKey . "\"></script>";
		$js[] = "<script>";
		$code = file_get_contents(dirname(__FILE__) . "/js/script.js");
		$js[] = str_replace("##SITE_KEY##", $this->siteKey, $code);
		$js[] = "</script>";
		$script = implode("\n", $js);
		return $html.$script;	//強制的に末尾に入れてみる
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
