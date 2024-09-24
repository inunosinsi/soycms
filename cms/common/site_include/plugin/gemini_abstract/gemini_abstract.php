<?php
GeminiAbstractPlugin::registerPlugin();

class GeminiAbstractPlugin {

	const PLUGIN_ID = "gemini_abstract";

	// @ToDo 200文字で要約するの箇所を設定出来るようにしたい
	private $number_of_characters = 200;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=> "記事概要自動生成プラグイン",
			"type" => Plugin::TYPE_ENTRY,
			"description"=> "Gemini APIを活用して、記事の概要を自動で生成します",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.7"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			SOY2::import("site_include.plugin.gemini_abstract.util.GeminiAbstractUtil");

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryListBeforeOutput', self::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));

			}else{
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));

				CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
			}
		}
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);
		
		if(count($entryIds)) GeminiAbstractUtil::setValuesByEntryIds($entryIds);
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){
		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];
		
		$fieldValues = GeminiAbstractUtil::getValuesByEntryId($entryId);
		$abstract = (isset($fieldValues["value"])) ? $fieldValues["value"] : "";

		if(strlen($abstract)){
			$abstract = GeminiAbstractUtil::getPrefix().$abstract.GeminiAbstractUtil::getPostfix();
		}else{
			$content = strip_tags(soycms_get_entry_object($entryId)->getContent());
			$abstract = mb_substr($content, 0, GeminiAbstractUtil::getCount());
		}

		$htmlObj->addLabel(GeminiAbstractUtil::FIELD_ID, array(
			"soy2prefix" => "cms",
			"html" => $abstract
		));
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		if((int)$entry->getIsPublished() !== 1) return;
		if(!isset($_POST["gemini_abstract_aute_create"]) || (int)$_POST["gemini_abstract_aute_create"] !== 1) return;
		

		// 記事を紐付けたブログページのIDを取得
		$blogPageId = GeminiAbstractUtil::getBlogPageIdByEntryId((int)$entry->getId());
		if($blogPageId === 0) return;

		$result = SOY2Logic::createInstance("site_include.plugin.gemini_abstract.logic.GeminiAbstractLogic")->generate($blogPageId, (int)$entry->getId());
		if(!strlen($result)) return;
		
		GeminiAbstractUtil::saveAbstract((int)$entry->getId(), $result);
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		foreach($args as $entryId){
			try{
				soycms_get_hash_table_dao("entry_attribute")->deleteByEntryId($entryId);
			}catch(Exception $e){

			}
		}
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
		$abstract = soycms_get_entry_attribute_value($entryId, GeminiAbstractUtil::FIELD_ID, "string");
		if(!strlen($abstract)) return "<input type=\"hidden\" name=\"gemini_abstract_aute_create\" value=\"1\">";

		$html = array();
		$html[] = "<div class=\"alert alert-success\">自動生成された概要</div>";
		$html[] = "<div class=\"form-group\">";
		$html[] = $abstract."<br>";
		$html[] = "<label><input type=\"checkbox\" name=\"gemini_abstract_aute_create\" value=\"1\"> 概要を再生正する</label>";
		$html[] = "</div>";

		return "<br>".implode("\n", $html);
	}

	function config_page($message){
		SOY2::import("site_include.plugin.".self::PLUGIN_ID.".config.GeminiAbstractConfigPage");
		$form = SOY2HTMLFactory::createInstance("GeminiAbstractConfigPage");
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
			$exist = $dao->executeQuery("SELECT * FROM EntryAttribute", array());
			return;//テーブル作成済み
		}catch(Exception $e){

		}

		$file = file_get_contents(dirname(dirname(__DIR__)) . "/CustomFieldAdvanced/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/create/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = trim("create" . $sql);
			try{
				$dao->executeUpdateQuery($sql, array());
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
		if(is_null($obj)) $obj = new GeminiAbstractPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj,"init"));
	}
}
