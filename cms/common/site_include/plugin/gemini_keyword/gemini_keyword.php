<?php
GeminiKeywordPlugin::registerPlugin();

class GeminiKeywordPlugin {

	const PLUGIN_ID = "gemini_keyword";
	const DEBUG = false;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=> "キーワード自動抽出プラグイン",
			"type" => Plugin::TYPE_ENTRY,
			"description"=> "Gemini APIを活用して、記事からキーワードを抽出して一覧を作成します",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co/article/6163",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.8"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			SOY2::import("site_include.plugin.gemini_keyword.util.GeminiKeywordUtil");

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
				/**
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
				**/
				//JSONの出力
				CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
				CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
			}else{
				CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
			
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}
		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	function onPageOutput($obj){
		$query = self::_getQueryByUri();

		$obj->addLabel("search_keyword", array(
			"soy2prefix" => "cms",
			"text" => $query
		));
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){
		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];
		/**
		//$keyword = GeminiKeywordUtil::getKeywordByEntryId($entryId);

		// $htmlObj->addLabel(GeminiKeywordUtil::FIELD_ID, array(
			// "soy2prefix" => "cms",
			// "html" => $abstract
		// ));
		**/
	}

	function onSiteAccess($obj){
		// http://domain/site_id/gemini_keyword.json
		if(!isset($_SERVER["PATH_INFO"]) || is_bool(strpos($_SERVER["PATH_INFO"], ".json"))) return;
		
		preg_match('/\/(.*?)\.json/', $_SERVER["PATH_INFO"], $tmp);
		if(!isset($tmp[1]) || $tmp[1] != self::PLUGIN_ID) return;

		$q = (isset($_POST["q"])) ? $_POST["q"] : null;
		if(is_null($q) && isset($_GET["q"])) $q = $_GET["q"];
		if(is_null($q)) self::_output();

		$q = trim((string)$q);
		if(!strlen($q)) self::_output();

		self::_output(
			SOY2Logic::createInstance("site_include.plugin.gemini_keyword.logic.GeminiKeywordLogic")->getCandidateKeywords($q)
		);
	}

	/**
	 * @param array
	 */
	private function _output(array $arr=array()){
		if(self::DEBUG){
			var_dump($arr);
		}else{
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($arr);
		}
		exit;
	}

	function onLoad(){
		$query = self::_getQueryByUri();
		if(!strlen($query)) return array();

		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
		

		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$soyId = PluginBlockUtil::getSoyIdByPageIdAndPluginId($pageId, self::PLUGIN_ID);
		if(!isset($soyId)) return array();

		//ラベルIDを取得とデータベースから記事の取得件数指定
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId, $soyId);
		if(is_null($labelId)) return array();

		return SOY2Logic::createInstance("site_include.plugin.gemini_keyword.logic.GeminiKeywordLogic")->search(
			$labelId,
			$query,
			PluginBlockUtil::getLimitByPageId($pageId, $soyId)
		);
	}

	private function _getQueryByUri(){
		if(isset($_GET["q"]) && strlen(trim($_GET["q"])) > 0){
			return htmlspecialchars(trim($_GET["q"]), ENT_QUOTES, "UTF-8");
		}
	
		$uri = trim((string)soycms_get_page_object((int)$_SERVER["SOYCMS_PAGE_ID"])->getUri());
		if(!strlen($uri)) return "";

		$q = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "/".$uri."/")+strlen($uri)+2);
		if(!strlen($q)) return "";

		if(soy2_strpos($q, "/") > 0) $q = substr($q, 0, soy2_strpos($q, "/"));
		return rawurldecode($q);
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		if((int)$entry->getIsPublished() !== 1) return;
		if(!isset($_POST["gemini_keyword_aute_extract"]) || (int)$_POST["gemini_keyword_aute_extract"] !== 1) return;
		
		// 記事を紐付けたブログページのIDを取得
		$blogPageId = GeminiKeywordUtil::getBlogPageIdByEntryId((int)$entry->getId());
		if($blogPageId === 0) return;

		SOY2Logic::createInstance("site_include.plugin.gemini_keyword.logic.GeminiKeywordLogic")->extractKeywordsAndSave($blogPageId, (int)$entry->getId());
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		if(!count($args)) return true;
	
		SOY2::import("site_include.plugin.gemini_keyword.util.GeminiKeywordUtil");
		GeminiKeywordUtil::deleteByEntryIds($args);
		return true;
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::_buildFormOnEntryPage($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::_buildFormOnEntryPage($entryId);
	}

	/**
	 * @param int
	 * @return string
	 */
	private function _buildFormOnEntryPage(int $entryId){
		$keywords = GeminiKeywordUtil::getKeywordsByEntryId($entryId);
		if(!count($keywords)) return "<input type=\"hidden\" name=\"gemini_keyword_aute_extract\" value=\"1\">";

		$html = array();
		$html[] = "<div class=\"alert alert-success\">自動抽出したキーワード</div>";
		$html[] = "<div class=\"form-group\">";
		foreach($keywords as $keyword){
			$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-default mt-3 mr-3\">".$keyword."</a>";
		}
		$html[] = "<br><label><input type=\"checkbox\" name=\"gemini_keyword_aute_extract\" value=\"1\"> キーワードを再抽出する</label>";
		$html[] = "</div>";

		return "<br>".implode("\n", $html);
	}

	function config_page($message){
		SOY2::import("site_include.plugin.".self::PLUGIN_ID.".config.GeminiKeywordConfigPage");
		$form = SOY2HTMLFactory::createInstance("GeminiKeywordConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM GeminiKeyword", array());
			return;//テーブル作成済み
		}catch(Exception $e){

		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/CREATE TABLE/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			try{
				$dao->executeQuery("CREATE TABLE ".$sql);
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new GeminiKeywordPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj,"init"));
	}
}
