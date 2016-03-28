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
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.2"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		//active or non active
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::setEvent("onEntryCreate",$this->getId(),array($this,"onEntryUpdate"));
			CMSPlugin::setEvent("onEntryUpdate",$this->getId(),array($this,"onEntryUpdate"));
			CMSPlugin::setEvent("onPageEdit",$this->getId(),array($this,"onPageEdit"));
			CMSPlugin::setEvent("onPageUpdate",$this->getId(),array($this,"onPageUpdate"));

			//@TODO 削除時
			//@TODO コピー時
			//CMSPlugin::setEvent("onEntryCopy",$this->getId(),array($this,"onEntryCopy"));
			CMSPlugin::setEvent("onSiteAccess",$this->getId(),array($this,"onSiteAccess"));
//			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID,"Entry.Detail",array($this,"onCallCustomField"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID,"Blog.Entry",array($this,"onCallCustomField_inBlog"));
		}

	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new URLShortenerPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
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

/*
	function onEntryCopy($ids){
		$oldId = $ids[0];
		$newId = $ids[1];

		if($this->useId){
			$entry = $this->getEntry($newId);
			if($entry){
				if($entry->isEmptyAlias() || $entry->getId() != $entry->getAlias()){
					$entry->setAlias($entry->getId());
					$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
					$logic->update($entry);
				}
			}
		}
	}
*/

	/**
	 * @TODO 記事画面からの削除
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		$shorten = @$_POST["urlShortener"];

		//空じゃなくて、半角英数字のみ
		if(!empty($shorten) && preg_match('/^[a-zA-Z0-9]+$/',$shorten)){
			$dao = SOY2DAOFactory::create("cms.URLShortenerDAO");

			//ユニーク
			try{
				$unique = $dao->getByFrom($shorten);
				$uniqueId = $unique->getId();
			}catch(Exception $e){
				$uniqueId = false;
			}

			try{

				$obj = $dao->getByTargetTypeANDTargetId(URLShortener::TYPE_ENTRY, $entry->getId());
				//変更なし
				if($shorten == $obj->setFrom) return;

				//ユニークチェック
				if($obj->getId()  == $uniqueId){
					$obj->setFrom($shorten);
					$obj->save();
				}

			}catch(Exception $e){
				if($uniqueId)return;//ユニーク

				//新規
				$arg = SOY2PageController::getArguments();
				$pageId = @$arg[0];
				$page = $this->getBlogPage($pageId);
				$entoryPageUri = $page->getEntryPageURL();

				$obj = new URLShortener();
				$obj->setFrom($shorten);
				$obj->setTo($entoryPageUri.$entry->getAlias());
				$obj->setTargetType(URLShortener::TYPE_ENTRY);
				$obj->setTargetId($entry->getId());
				try{
					$obj->save();
				}catch(Exception $e){

				}

			}

		}

	}

	function onSiteAccess($args){
		$contoller = $args["controller"];

		$param = implode("/", $contoller->args);

		SOY2::import("domain.cms.URLShortener");
		$dao = SOY2DAOFactory::create("cms.URLShortenerDAO");

		try{
			$from = $dao->getByFrom($param);
		}catch(Exception $e){

			return;// not matching
		}

		$uri = $from->getTo();
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

/*
	function onCallCustomField(){
		if($this->useId){
			$html = "";
		}else{
			$arg = SOY2PageController::getArguments();
			$entryId = @$arg[0];
			$alias = $this->getAlias($entryId);

			$html = "<div class=\"section custom_alias\">";
			$html .= "<p class=\"sub\"><label for=\"custom_alias_input\">カスタムエイリアス（ブログのエントリーページのURL）</label></p>";
			$html .= "<input value=\"".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")."\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" style=\"width:400px\" />";
			$html .= "</div>";
		}
		return $html;
	}
*/

	function onCallCustomField_inBlog(){
		if($this->useId){
			$html = "";
		}else{
			$arg = SOY2PageController::getArguments();
			$pageId = @$arg[0];
			$entryId = @$arg[1];

			$page = $this->getBlogPage($pageId);
			$shorten = $this->getEntryURLShortener($entryId);

			$siteUrl = UserInfoUtil::getSitePublishURL();

			$shortenUrl = "";
			if(strlen($shorten)>0){
				$shortenUrl = $siteUrl.$shorten;
			}

			$html = array();
			if($page){
				$entryPageUri = UserInfoUtil::getSitePublishUrl();
				$entryUri = $entryPageUri.rawurlencode($shorten);

				$html[] = "<div class=\"section custom_alias\">";
				$html[] = "<p class=\"sub\"><label for=\"url_shortener_input\">短縮URL</label></p>";
				$html[] = $siteUrl;
				$html[] = "<input value=\"".htmlspecialchars($shorten, ENT_QUOTES, "UTF-8")."\" id=\"url_shortener_input\" name=\"urlShortener\" type=\"text\" style=\"width:300px\" />";
				$html[] = "<a href=\"".htmlspecialchars($entryUri, ENT_QUOTES, "UTF-8")."\" target=\"_blank\">確認</a>";

				if(strlen($shortenUrl)>0){
					$html[] = "<p>";
					$html[] = "コピーしてお使いください&nbsp;:&nbsp;<input type=\"\" value=\"".$shortenUrl."\" size=\"60\" onclick=\"this.select()\" readonly=\"readonly\" />";
					$html[] = "</p>";
				}

				$html[] = "</div>";
			}


		}
		return implode("\n",$html);
	}

	/**
	 * 編集画面が呼び出されたとき
	 */
	function onPageEdit($arg){
		$object = $arg["page"];
		$from = $this->getPageURLShortener($object->id);

		$object->createAdd("url_shortener_input","HTMLInput",array(
			"name" => "urlShortener",
			"value" => $from
		));

		return true;
	}

	/**
	 * ページが編集される時
	 * @TODO ページ編集画面からの削除
	 */
	function onPageUpdate($arg){
		if(isset($_POST["urlShortener"])){
			$dao = SOY2DAOFactory::create("cms.URLShortenerDAO");
			$page = $arg["new_page"];
			$shorten = @$_POST["urlShortener"];

			try{
				$unique = $dao->getByFrom($shorten);
				$uniqueId = $unique->getId();
			}catch(Exception $e){
				$uniqueId = false;
			}



			try{
				$obj = $dao->getByTargetTypeANDTargetId(URLShortener::TYPE_ENTRY, $page->getId());
				//変更なし
				if($shorten == $obj->setFrom) return;

				//ユニークチェック
				if($obj->getId()  == $uniqueId){
					$obj->setFrom($shorten);
					$obj->save();
				}

			}catch(Exception $e){
				if($uniqueId)return;//ユニーク

				$obj = new URLShortener();
				$obj->setFrom($shorten);
				$obj->setTo($page->getUri());
				$obj->setTargetType(URLShortener::TYPE_PAGE);
				$obj->setTargetId($page->getId());
				try{
					$obj->save();
				}catch(Exception $e){

				}

			}

		}
	}

	function getEntry($entryId){
		try{
			$dao = SOY2DAOFactory::create("cms.EntryDAO");
			$entry = $dao->getById($entryId);
		}catch(Exception $e){
			return null;
		}
		return $entry;
	}

	function getAlias($entryId){
		$entry = $this->getEntry($entryId);
		if($entry){
			return $entry->getAlias();
		}else{
			return $entryId;
		}
	}

	function getBlogPage($pageId){
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	try{
    		$page = $dao->getById($pageId);
    	}catch(Exception $e){
    		return null;
    	}
    	return $page;
	}

	function setUseId($useId){
		$this->useId = $useId;
	}
	public function getVersion() {
		return $this->version;
	}
	public function setVersion($version) {
		$this->version = $version;
	}

	/**
	 * @param String targetId
	 */
	function getEntryURLShortener($targetId){
		$dao = SOY2DAOFactory::create("cms.URLShortenerDAO");

		try{
			$obj = $dao->getByTargetTypeANDTargetId(URLShortener::TYPE_ENTRY, $targetId);
		}catch(Exception $e){
			$obj = new URLShortener();
		}

		return $obj->getFrom();
	}

	/**
	 * @param String targetId
	 */
	function getPageURLShortener($targetId){
		$dao = SOY2DAOFactory::create("cms.URLShortenerDAO");

		try{
			$obj = $dao->getByTargetTypeANDTargetId(URLShortener::TYPE_PAGE, $targetId);
		}catch(Exception $e){
			$obj = new URLShortener();
		}

		return $obj->getFrom();
	}
}
?>