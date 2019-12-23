<?php

TagCloudPlugin::register();

class TagCloudPlugin{

	const PLUGIN_ID = "TagCloud";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"タグクラウドプラグイン",
			"description"=>"タグクラウド",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.6"
		));

		//active or non active
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config_page"
			));

			if(!defined("_SITE_ROOT_")){	//管理画面側
				CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));

				SOY2::import("site_include.plugin.tag_cloud.component.CustomFieldForm");
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));

				CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
			}else{
				CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
			}
		} else {
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.tag_cloud.config.TagCloudBlockPage");
        $form = SOY2HTMLFactory::createInstance("TagCloudBlockPage");
        $form->setPluginObj($this);
        $form->execute();
        return $form->getObject();
	}

	/**
	 * @TODO 記事画面からの削除
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];

		//登録されているタグを一旦削除
		SOY2::imports("site_include.plugin.tag_cloud.domain.*");
		$linkDao = SOY2DAOFactory::create("TagCloudLinkingDAO");
		try{
			$linkDao->deleteByEntryId($entry->getId());
		}catch(Exception $e){
			//
		}

		if(isset($_POST["TagCloudPlugin"]["tag"]) && strlen($_POST["TagCloudPlugin"]["tag"])){
			$tagStr = str_replace("、", ",", trim($_POST["TagCloudPlugin"]["tag"]));
			$tags = explode(",", $tagStr);
			if(count($tags)){
				$dicDao = SOY2DAOFactory::create("TagCloudDictionaryDAO");
				foreach($tags as $tag){
					$tag = trim($tag);
					try{
						$tagObj = $dicDao->getByWord($tag);
						$wordId = $tagObj->getId();
					}catch(Exception $e){
						$tagObj = new TagCloudDictionary();
						$tagObj->setWord($tag);
						try{
							$wordId = $dicDao->insert($tagObj);
						}catch(Exception $e){
							$wordId = null;
						}
					}

					if(isset($wordId)){
						$linkObj = new TagCloudLinking();
						$linkObj->setEntryId($entry->getId());
						$linkObj->setWordId($wordId);
						try{
							$linkDao->insert($linkObj);
						}catch(Exception $e){
							//
						}
					}
				}
			}

		}
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return CustomFieldForm::buildForm($entryId);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return CustomFieldForm::buildForm($entryId);
	}

	function onLoad(){
		if(!isset($_GET["tagcloud"]) || !is_numeric($_GET["tagcloud"])) return array();
		$wordId = (int)$_GET["tagcloud"];

		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");

        $pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

        //ラベルIDを取得とデータベースから記事の取得件数指定
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
		if(is_null($labelId)) return array();

        $count = PluginBlockUtil::getLimitByPageId($pageId);
        $entryDao = SOY2DAOFactory::create("cms.EntryDAO");
        $sql = "SELECT ent.* FROM Entry ent ".
             "JOIN EntryLabel lab ".
             "ON ent.id = lab.entry_id ".
			 "JOIN TagCloudLinking lnk ".
			 "ON ent.id = lnk.entry_id ".
             "WHERE ent.openPeriodStart < " . time() . " ".
             "AND ent.openPeriodEnd >= " .time() . " ".
             "AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
			 "AND lab.label_id = :labelId ".
			 "AND lnk.word_id = :wordId ".
			 "GROUP BY ent.id ".
			 "ORDER BY ent.cdate DESC ";
        $binds = array(":labelId" => $labelId, ":wordId" => $wordId);

        if(isset($count) && $count > 0) {
            $sql .= "Limit " . $count;
        }

        try{
            $res = $entryDao->executeQuery($sql, $binds);
        }catch(Exception $e){
            $res = array();
        }

        if(!count($res)) return array();

        $entries = array();
        foreach($res as $v){
            $entries[] = $entryDao->getObject($v);
        }

        return $entries;
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM TagCloudDictionary", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/CREATE TABLE/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = "create table " . trim($sql);
			try{
				$dao->executeQuery($sql);
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new TagCloudPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
