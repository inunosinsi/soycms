<?php

UrlShortenerPlugin::register();

class UrlShortenerPlugin{

	const PLUGIN_ID = "UrlShortener";

	public $useId;
	public $version;

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"短縮URLプラグイン",
			"description"=>"ページやブログの記事ページの短縮URLを設定することが出来ます。<br>SOY CMS 1.3.1以上で動作します。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.5"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		//active or non active
		//そもそもsetEventはonActive以外activeじゃないと無視されるのでactiveCheckは不要
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.url_shortener.domain.URLShortenerDAO");

			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent("onSiteAccess",$this->getId(),array($this,"onSiteAccess"));
			}else{
				CMSPlugin::setEvent("onEntryCreate",$this->getId(),array($this,"onEntryUpdate"));
				CMSPlugin::setEvent("onEntryUpdate",$this->getId(),array($this,"onEntryUpdate"));
				CMSPlugin::setEvent("onPageUpdate",$this->getId(),array($this,"onPageUpdate"));
				CMSPlugin::setEvent("onPageEdit",$this->getId(),array($this,"onPageEdit"), array(), true);
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID,"Blog.Entry",array($this,"onCallCustomField_inBlog"));
			}
		}
	}

	/**
	 * @todo add when update
	 * @return Array(YYYYmmdd...) history
	 */
	public static function getVersionHistory(){
		$history = array();
		$history[] = "20110214";//init
		$history[] = "20110215";//init
		return $history;
	}

	/**
	 * @return String YYYYmmdd
	 */
	public static function getLatestVersion(){
		$history = self::getVersionHistory();
		rsort($history);
		return $history[0];
	}

	function getId(){
		return self::PLUGIN_ID;
	}

	/**
	 * @TODO 記事画面からの削除
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		$shorten = (isset($_POST["urlShortener"]) && is_string($_POST["urlShortener"])) ? trim($_POST["urlShortener"]) : "";

		$dao = SOY2DAOFactory::create("URLShortenerDAO");

		//空じゃなくて、半角英数字のみ
		if(strlen($shorten) && preg_match('/^[a-zA-Z0-9]+$/',$shorten)){
			try{
				$shortenObj = $dao->getByTargetTypeANDTargetId(URLShortener::TYPE_ENTRY, $entry->getId());
			}catch(Exception $e){
				$shortenObj = new URLShortener();
				$shortenObj->setTargetType(URLShortener::TYPE_ENTRY);
				$shortenObj->setTargetId($entry->getId());
			}

			// 新規の場合にオブジェクトに入れる値がある
			if(!is_numeric($shortenObj->getId())){
				$arg = SOY2PageController::getArguments();
				$shortenObj->setTo(soycms_get_blog_page_object((int)$arg[0])->getEntryPageURL().$entry->getAlias());
			}

			$shortenObj->setFrom($shorten);

			try{
				$dao->insert($shortenObj);
			}catch(Exception $e){
				try{
					$dao->update($shortenObj);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				$dao->deleteByTargetTypeANDTargetId(URLShortener::TYPE_ENTRY, $entry->getId());
			}catch(Exception $e){
				//
			}
		}
	}

	function onSiteAccess($args){
		$contoller = $args["controller"];
		$param = implode("/", $contoller->args);
		
		try{
			$uri = SOY2DAOFactory::create("URLShortenerDAO")->getByFrom($param)->getTo();
		}catch(Exception $e){
			return;// not matching
		}

		$siteConfig = $contoller->siteConfig;
		header("HTTP/1.1 301 Moved Permanently");
		header("Content-Type: text/html; charset=".$siteConfig->getCharsetText());
		header("Location: ".CMSPageController::createRelativeLink($uri,true));
		echo "<html>";
		echo "<head></head>";
		echo "<body></body>";
		echo "</html>";

		exit;
	}

	function onCallCustomField_inBlog(){
		if($this->useId){
			$html = "";
		}else{
			$arg = SOY2PageController::getArguments();
			$pageId = (isset($arg[0])) ? (int)$arg[0] : 0 ;
			$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;

			$page = soycms_get_blog_page_object($pageId);
			$shorten = $this->getEntryURLShortener($entryId);

			$siteUrl = UserInfoUtil::getSitePublishURL();

			$shortenUrl = "";
			if(strlen($shorten)) $shortenUrl = $siteUrl.$shorten;

			$html = array();
			if($page){
				$entryPageUri = UserInfoUtil::getSitePublishUrl();
				$entryUri = $entryPageUri.rawurlencode($shorten);

				$html[] = "<div class=\"form-group custom_alias\">";
				$html[] = "<label for=\"url_shortener_input\">短縮URL</label>";
				$html[] = "<div class=\"form-inline\">";
				$html[] = $siteUrl;
				$html[] = "<input value=\"".htmlspecialchars($shorten, ENT_QUOTES, "UTF-8")."\" id=\"url_shortener_input\" name=\"urlShortener\" type=\"text\" class=\"form-control\" style=\"width:300px\" />";
				if(strlen($shorten)) {
					$html[] = "<a href=\"".htmlspecialchars($entryUri, ENT_QUOTES, "UTF-8")."\" target=\"_blank\" class=\"btn btn-primary btn-sm\" rel=\"noopener\">確認</a>";
					$html[] = "&nbsp;";
					$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-warning btn-sm\" rel=\"noopener\" onclick=\"copyUrl('".$shortenUrl."')\">短縮URLのコピー</a>";
					$html[] = "<script>" . file_get_contents(dirname(__FILE__) . "/js/copy.js") . "</script>";
				}

				$html[] = "</div>";
				$html[] = "</div>";
			}
		}
		return implode("\n",$html);
	}

	/**
	 * 編集画面が呼び出されたとき
	 */
	function onPageEdit($arg){
		$page = $arg["page"];

		//短縮URLの表示
		$page->addModel("url_shortener_display", array(
				"visible" => CMSPlugin::activeCheck(self::PLUGIN_ID),
		));

		//入力欄
		$page->addInput("url_shortener_input", array(
			"name" => "urlShortener",
			"value" => $this->getPageURLShortener($page->getId()),
		));

		return true;
	}

	/**
	 * ページが編集される時
	 * @TODO ページ編集画面からの削除
	 */
	function onPageUpdate($arg){
		if(!isset($_POST["urlShortener"])) return;
		$dao = SOY2DAOFactory::create("URLShortenerDAO");
		$page = $arg["new_page"];
		$shorten = trim($_POST["urlShortener"]);

		//空じゃなくて、半角英数字のみ
		if(strlen($shorten) && preg_match('/^[a-zA-Z0-9]+$/',$shorten)){
			try{
				$shortenObj = $dao->getByTargetTypeANDTargetId(URLShortener::TYPE_PAGE, $page->getId());
			}catch(Exception $e){
				$shortenObj = new URLShortener();
				$shortenObj->setTargetType(URLShortener::TYPE_PAGE);
				$shortenObj->setTargetId($page->getId());
				$shortenObj->setTo($page->getUri());
			}

			$shortenObj->setFrom($shorten);

			try{
				$dao->insert($shortenObj);
			}catch(Exception $e){
				try{
					$dao->update($shortenObj);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				$dao->deleteByTargetTypeANDTargetId(URLShortener::TYPE_PAGE, $page->getId());
			}catch(Exception $e){
				//
			}
		}
	}

	function getAlias(int $entryId){
		$entry = soycm_get_entry_object($entryId);
		return (is_string($entry->getAlias())) ? $entry->getAlias() : $entryId;
	}

	function setUseId($useId){
		$this->useId = $useId;
	}
	function getVersion() {
		return $this->version;
	}
	function setVersion($version) {
		$this->version = $version;
	}

	/**
	 * @param String targetId
	 */
	function getEntryURLShortener($targetId){
		try{
			return SOY2DAOFactory::create("URLShortenerDAO")->getByTargetTypeANDTargetId(URLShortener::TYPE_ENTRY, $targetId)->getFrom();
		}catch(Exception $e){
			return "";
		}
	}

	/**
	 * @param String targetId
	 */
	function getPageURLShortener($targetId){
		try{
			return SOY2DAOFactory::create("URLShortenerDAO")->getByTargetTypeANDTargetId(URLShortener::TYPE_PAGE, $targetId)->getFrom();
		}catch(Exception $e){
			return "";
		}
	}

	function config_page($message){
		$version = $this->getVersion();
		if(is_null($version)){
			//初期化
			include_once(dirname(__FILE__)."/page/_init/config_form.php");
			$form = SOY2HTMLFactory::createInstance("URLShortenerPluginInitFormPage");

		}else if(!is_null($version) && $version != self::getLatestVersion()){
			//アップデート @TODO 世代越えバージョンアップ
			include_once(dirname(__FILE__)."/page/_update/config_form.php");
			$form = SOY2HTMLFactory::createInstance("URLShortenerPluginFormPage");

		}else{
			//通常更新
			include_once(dirname(__FILE__)."/page/config_form.php");
			$form = SOY2HTMLFactory::createInstance("URLShortenerPluginFormPage");

		}

		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new URLShortenerPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
