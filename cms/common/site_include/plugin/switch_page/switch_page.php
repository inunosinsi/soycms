<?php

class SwitchPagePlugin{

	const PLUGIN_ID = "switch_page";

	// array(idx => fromのuri)の形式　idxには詳細設定があり、DataSetに格納する
	private $fromList = array();
	private $toList = array();

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ページ切り替えプラグイン",
			"description"=>"任意のページで期間設定により、URIそのままで他のページの読み込みを切り替える",
			"author"=>"saitodev.co",
			"url"=>"https://saitodev.co/article/3622",
			"mail"=>"tsuyoshi saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onPathInfoBuilder', self::PLUGIN_ID, array($this,"onPathInfoBuilder"));
				CMSPlugin::setEvent("onSiteAccess", self::PLUGIN_ID, array($this, "onSiteAccess"));
			}
		}
	}

	function onPathInfoBuilder($arg){
		if(!is_array($this->fromList) || !count($this->fromList)) return array();

		$idx = array_search($arg["uri"], $this->fromList);
		if(is_bool($idx)) return array();

		//設定がある場合は設定内容を取得
		SOY2::import("site_include.plugin.switch_page.util.SwitchPageUtil");
		$cnfs = SwitchPageUtil::getConfig();
		if(!isset($cnfs[$idx])) return array();

		//公開期間を見る
		$now = time();
		if($cnfs[$idx]["start"] >= $now || $cnfs[$idx]["end"] <= $now) return array();

		//URIの上書き
		$uri = SwitchPageUtil::getPageUriByPageId($cnfs[$idx]["to"]);
		return array("uri" => $uri, "args" => $arg["args"]);
	}

	function onSiteAccess($args){
		if(!is_array($this->toList) || !count($this->toList)) return;

		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";
		$pathInfo = preg_replace('/^\/|\/$/', "", $pathInfo);

		$idx = array_search($pathInfo, $this->toList);
		if(is_bool($idx)) return;

		//同一ブラウザ　別タブで管理画面を開いている時は以後の処理を行わない
		$session = SOY2ActionSession::getUserSession();
		if(!is_null($session->getAttribute("loginid"))) return;

		//設定がある場合は設定内容を取得
		SOY2::import("site_include.plugin.switch_page.util.SwitchPageUtil");
		$cnfs = SwitchPageUtil::getConfig();
		if(!isset($cnfs[$idx])) return;

		//設定がある場合は、該当ページは出力しない
		$uri = SwitchPageUtil::getPageUriByPageId($cnfs[$idx]["from"]);
		$siteConfig = $args["controller"]->siteConfig;

		header("HTTP/1.1 301 Moved Permanently");
		header("Content-Type: text/html; charset=".$siteConfig->getCharsetText());
		header("Location: ".CMSPageController::createRelativeLink($uri,true));
		echo "<html>";
		echo "<head></head>";
		echo "<body></body>";
		echo "</html>";
		exit;
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.switch_page.config.SwitchPageConfigPage");
		$form = SOY2HTMLFactory::createInstance("SwitchPageConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function setFromList($fromList){
		$this->fromList = $fromList;
	}

	function setToList($toList){
		$this->toList = $toList;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new SwitchPagePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

SwitchPagePlugin::register();
