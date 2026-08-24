<?php

class FailureToCheckPlugin{

	const PLUGIN_ID = "0_inquiry_failure_to_check";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "お問い合わせ確認漏れ通知プラグイン",
			"type" => Plugin::TYPE_SOYAPP,
			"description" => "SOY Inquiryのフォームにあったお問い合わせで確認していないものがあれば通知する。",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"1.1"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//管理側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::setEvent("onAdminTop", self::PLUGIN_ID, array($this, "onAdminTop"));
			}

        //プラグインの初回動作
		}
	}

	function onAdminTop(){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");
		$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
		
		try{
			$res = $dao->executeQuery(
				"SELECT id FROM soyinquiry_inquiry ".
				"WHERE flag = " . SOYInquiry_Inquiry::FLAG_NEW." ".
				"LIMIT 1"
			);
		}catch(Exception $e){
			$res = aray();
		}
		SOYAppUtil::resetAppMode($old);

		if(isset($res[0]["id"])){
			return array(
				"title" => "SOY Inquiryからの通知",
				"content" => "<div class=\"alert alert-danger\" style=\"margin:5px 20px;\">未確認のお問い合わせがあります。</div>"
			);
		}else{
			return array();
		}
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new FailureToCheckPlugin();

			//この時プラグインを強制的に有効にする
			$filepath = CMSPlugin::getSiteDirectory().'/.plugin/'. self::PLUGIN_ID;
			if(!file_exists($filepath . ".inited") && file_exists(SOY2::RootDir()."db/inquiry.db")){
				@file_put_contents($filepath .".active","active");
				@file_put_contents($filepath .".inited","inited");
			}
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

FailureToCheckPlugin::register();
