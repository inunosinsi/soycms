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

	
	function getId(){
		return self::PLUGIN_ID;	
	}
	
	function init(){
		
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ブログ記事SEOプラグイン",
			"description"=>"ブログページの記事毎ページにkeywordとdescriptionを記事投稿時に追加する",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.5.2"
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
		$entryId = @$arg[0];
		
		list($keyword,$description) = $this->getEntryInfo($entryId);
		
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
		$entryId = @$arg[1];
		
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
	function getEntryInfo($entryId){
		$dao = new SOY2DAO();
		
		try{
			$result = $dao->executeQuery("select keyword,description from Entry where id = :id",
				array(":id"=>$entryId));
				
			if(count($result)<1){
				return array("","");
			}
			
			return array($result[0]["keyword"],$result[0]["description"]);
			
		}catch(Exception $e){
			return array("","");
		}
	}
	
	
	/**
	 * キーワードに何も入力していないときにブログトップからキーワードを読み込む
	 */
	function getKeyword(){

		$getUrl = $_SERVER["REQUEST_URI"];
		$getUrl = substr($getUrl, 0, strrpos($getUrl ,"/"));
		$getUrl = substr($getUrl, 0, strrpos($getUrl ,"/"));	
		$getTopUrl = "http://" . $_SERVER["SERVER_NAME"] . $getUrl;
		$getHtml = file_get_contents($getTopUrl);
		
		$getText = substr($getHtml, strpos($getHtml, "keywords"));
		$getText = substr($getText, strpos($getText, "="));
		$getText = substr($getText, "2");
		$getKeyword = substr($getText, "0", strpos($getText, "\""));
		
		return $getKeyword;
	
	}


	/**
	 * 概要に何も入力していないときにブログトップから概要を読み込む
	 */	
	function getDescription($getDescription){
		
		$getUrl = $_SERVER["REQUEST_URI"];
		$getUrl = substr($getUrl, 0, strrpos($getUrl ,"/"));
		$getUrl = substr($getUrl, 0, strrpos($getUrl ,"/"));	
		$getTopUrl = "http://" . $_SERVER["SERVER_NAME"] . $getUrl;
		$getHtml = file_get_contents($getTopUrl);
				
		$getText = substr($getHtml, strpos($getHtml, "description"));
		$getText = substr($getText, strpos($getText, "="));
		$getText = substr($getText, "2");
		$getDescription = substr($getText, "0", strpos($getText, "\""));
		
		return $getDescription;
	}
	
	
	
	/**
	 * 公開側の出力
	 */
	function onPageOutput($obj){
		
		//ブログではない時は動作しません
		if(false == ($obj instanceof CMSBlogPage)){
			return;
		}
		
		//エントリー表示画面以外では動作しません
		if($obj->mode != CMSBlogPage::MODE_ENTRY){
			return;
		}
		
		
		$entry = $obj->entry;
		if(is_null($entry))$entry = new Entry();
		
		//データ取得
		list($keyword,$description) = $this->getEntryInfo($entry->getId());
		
		if(strlen($keyword) == "0"){
			
			$keyword = @$this->getKeyword();
			
		}
		
		if(strlen($description) == "0"){
			
			$description = @$this->getDescription();
			
		}
		
		$obj->createAdd("entry_keyword","HTMLModel",array(
			"soy2prefix" => "b_block",
			"attr:name" => "keywords",
			"attr:content" => $keyword
		));
		
		$obj->createAdd("entry_description","HTMLModel",array(
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
?>
