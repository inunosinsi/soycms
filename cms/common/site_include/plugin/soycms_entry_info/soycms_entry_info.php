<?php
/*
 * Created on 2009/10/08
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
EntryInfoPlugin::register();

class EntryInfoPlugin{

	const PLUGIN_ID = "soycms_entry_info";
	const MODE_REACQUIRE = 1;	//記事詳細でメタ情報が無い場合はトップページから再取得する
	const MODE_NONE = 0;	//記事詳細でメタ情報が無い場合はトップページから再取得しない
	private $mode = self::MODE_REACQUIRE;


	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){

		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ブログ記事SEOプラグイン",
			"description"=>"ブログページの記事毎ページにkeywordとdescriptionを記事投稿時に追加する",
			"author"=>"株式会社Brassica",
			"url"=>"http://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.8.1"
		));
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck($this->getId())){

			if(!defined("_SITE_ROOT_")){

				//記事作成時にキーワードとdescriptinをDBに挿入する
				CMSPlugin::setEvent('onEntryUpdate',$this->getId(),array($this,"onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate',$this->getId(),array($this,"onEntryUpdate"));

				//記事複製時にキーワードとdescriptionもDBに挿入する
				CMSPlugin::setEvent('onEntryCopy',CustomFieldPlugin::PLUGIN_ID,array($this,"onEntryCopy"));

				//記事投稿画面にフォームを表示する
				CMSPlugin::addCustomFiledFunction($this->getId(),"Entry.Detail",array($this,"onCallCustomField"));
				CMSPlugin::addCustomFiledFunction($this->getId(),"Blog.Entry", array($this, "onCallCustomField_inBlog"));

			//公開側設定
			}else{

				//公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));

			}

		}else{

			//プラグイン有効直前で新しいテーブルを追加する
			CMSPlugin::setEvent('onActive',$this->getId(),array($this,"createTable"));
		}


	}


	/**
	 * @TODO ヘルプを表示
	 */
	function config_page(){

		ob_start();
		include_once(dirname(__FILE__) . "/config.php");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * 記事作成画面にフォームを表示する
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		list($keyword, $description) = self::getEntryInfo($entryId);

		ob_start();
		include(dirname(__FILE__) . "/keywordForm.php");
		$html = ob_get_contents();
		ob_end_clean();

		echo $html;
	}

	/**
	 * ブログ記事作成画面にフォームを表示する
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		list($keyword, $description) = self::getEntryInfo($entryId);

		list($keyword,$description) = $this->getEntryInfo($entryId);

		ob_start();
		include(dirname(__FILE__) . "/keywordForm.php");
		$html = ob_get_contents();
		ob_end_clean();

		echo $html;
	}


	/**
	 * エントリー更新
	 */
	function onEntryUpdate($arg){

		$description = $_POST["description"];
		$keyword = $_POST["keyword"];

		$entry = $arg["entry"];

		$arg = SOY2PageController::getArguments();
		$dao = new SOY2DAO();

		try{
			$dao->executeUpdateQuery("update Entry set keyword = :custom where Entry.id = :id",
				array(
					":id"=>$entry->getId(),
					":custom"=>$keyword
					));
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * キーワードとdescriptionを取得
	 *
	 * @return array(keyword,description)
	 */
	private function getEntryInfo($entryId){
		if(!isset($entryId)) return array("", "");

		$dao = new SOY2DAO();

		try{
			$result = $dao->executeQuery("select keyword,description from Entry where id = :id",
				array(":id" => $entryId));

		}catch(Exception $e){
			return array("", "");
		}

		if(count($result) < 1) return array("", "");

		return array($result[0]["keyword"], $result[0]["description"]);
	}

	//概要に何も入力していないときにブログトップからキーワードか概要を読み込む
	private function getBlogTopMetaValue($type="keywords"){
		$html = self::getBlogTopHeadHTML();
		if(!strlen($html) || !strpos($html, $type)) return "";

		$text = substr($html, strpos($html, $type));
		$text = substr($text, strpos($text, "=") + 2);
		return trim(substr($text, "0", strpos($text, "\"")), "\"");
	}

	private function getBlogTopHeadHTML(){
		static $html;
		if(is_null($html)){
			$url = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"] ,"/"));
			$url = substr($url, 0, strrpos($url ,"/"));
			$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
			$html = @file_get_contents($http . "://" . $_SERVER["HTTP_HOST"] . $url);
			if(!$html){
				$html = "";
			}else{
				$html = substr($html, 0, strpos($html, "</head>"));
				$html = substr($html, strpos($html, "<head") + 5);
				if(strpos($html, "<link")){
					$html = preg_replace('/<link.*>/', "", $html);
				}
				if(strpos($html, "<script")){
					$html = preg_replace('/<script.*\/script>/s', "", $html);
				}
				if(strpos($html, "<style")){
					$html = preg_replace('/<style.*\/style>/s', "", $html);
				}
				if(strpos($html, "<title")){
					$html = preg_replace('/<title.*\/title>/s', "", $html);
				}

				$html = trim($html);
			}
		}

		return $html;
	}


	/**
	 * 公開側の出力
	 */
	function onPageOutput($obj){

		//ブログではない時 or エントリー表示画面以外では動作しません
		if(false == ($obj instanceof CMSBlogPage) || $obj->mode != CMSBlogPage::MODE_ENTRY) return;

		$entry = $obj->entry;
		if(is_null($entry)) $entry = new Entry();

		//データ取得
		list($keyword, $description) = self::getEntryInfo($entry->getId());

		if($this->mode == self::MODE_REACQUIRE){
			if(is_null($keyword) || strlen($keyword) === "0") $keyword = self::getBlogTopMetaValue("keywords");
			if(is_null($description) || strlen($description) === "0") $description = self::getBlogTopMetaValue("description");
		}

		$obj->addModel("is_entry_keyword", array(
			"soy2prefix" => "b_block",
			"visible" => (isset($keyword) && strlen($keyword))
		));

		$obj->addModel("entry_keyword", array(
			"soy2prefix" => "b_block",
			"attr:name" => "keywords",
			"attr:content" => $keyword
		));

		$obj->addModel("is_entry_description", array(
			"soy2prefix" => "b_block",
			"visible" => (isset($description) && strlen($description))
		));

		$obj->addModel("entry_description", array(
			"soy2prefix" => "b_block",
			"attr:name" => "description",
			"attr:content" => $description
		));
	}

	/**
	 * エントリーの複製時
	 */
	function onEntryCopy($args){
		list($old,$new) = $args;

		try{
			$dao = new SOY2DAO();
			$getKey = $dao->executeQuery("SELECT keyword FROM Entry WHERE Entry.id = :id",
											array(
											":id" =>$old
											));

			$dao->executeQuery("update Entry set keyword = :custom where Entry.id = :id",
					array(
						":id"=>$new,
						":custom"=>$getKey[0]["keyword"]
						));
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * DBに新しいテーブルの追加
	 */
	function createTable(){
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("alter table Entry add keyword text",array());
		}catch(Exception $e){
			//
		}

		return;
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new EntryInfoPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
