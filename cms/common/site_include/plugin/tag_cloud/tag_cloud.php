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
			"version"=>"1.8"
		));

		//active or non active
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config_page"
			));

			if(!defined("_SITE_ROOT_")){	//管理画面側
				//ハッシュ値が登録されているか？調べる
				SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
				TagCloudUtil::setHash();

				CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));

				SOY2::import("site_include.plugin.tag_cloud.component.TagCloudCustomFieldForm");
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));

				CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
			}else{
				CMSPlugin::setEvent("onEntryOutput", self::PLUGIN_ID, array($this,"onEntryOutput"));

				//公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput',self::PLUGIN_ID,array($this,"onPageOutput"));
				CMSPlugin::setEvent('onPageTitleFormat',self::PLUGIN_ID,array($this,"onPageTitleFormat"));

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

	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
		$tags = (is_numeric($entryId) && $entryId > 0) ? TagCloudUtil::getRegisterdTagsByEntryId($entryId) : array();

		$cnt = count($tags);
		$htmlObj->addModel("no_tag_cloud", array(
			"soy2prefix" => "cms",
			"visible" => ($cnt === 0)
		));

		$htmlObj->addModel("is_tag_cloud", array(
			"soy2prefix" => "cms",
			"visible" => ($cnt > 0)
		));

		SOY2::import("site_include.plugin.tag_cloud.component.TagCloudTagListComponent");
		$htmlObj->createAdd("tag_cloud_tag_list", "TagCloudTagListComponent", array(
			"soy2prefix" => "cms",
			"list" =>  $tags,
			"url" => ($cnt > 0) ? TagCloudUtil::getPageUrlSettedTagCloudBlock() : ""
		));
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
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return TagCloudCustomFieldForm::buildForm($entryId);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return TagCloudCustomFieldForm::buildForm($entryId);
	}

	function onLoad(){
		$wordId = self::_getWordIdFromGetParam();
		if(is_null($wordId)) return array();

		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

		$soyId = PluginBlockUtil::getSoyIdByPageIdAndPluginId($pageId, self::PLUGIN_ID);
		if(!is_string($soyId)) return array();

		//ラベルIDを取得とデータベースから記事の取得件数指定
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId, $soyId);
		if(is_null($labelId)) return array();

		return SOY2Logic::createInstance("site_include.plugin.tag_cloud.logic.TagCloudBlockEntryLogic")->search($labelId, $wordId, PluginBlockUtil::getLimitByPageId($pageId, $soyId));
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	function onPageOutput($obj){
		$wordId = self::_getWordIdFromGetParam();
		$tag = ((is_string($wordId) && strlen($wordId)) || (is_numeric($wordId) && $wordId > 0)) ? self::_getTagByWordId($wordId) : "";

		$obj->addLabel("tag_cloud_tag", array(
			"soy2prefix" => "cms",
			"text" => $tag
		));

		/** ページャ **/

		$current = 0;
		$last_page_number = 1;
		$url = null;

		//タグがない場合
		if(strlen($tag)) {
			SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");

			//最初にタグクラウド用のプラグインブロックがあるか調べる
			$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
			$soyId = PluginBlockUtil::getSoyIdByPageIdAndPluginId($pageId, self::PLUGIN_ID);
			if(!isset($soyId)) return;

			$logic = SOY2Logic::createInstance("site_include.plugin.tag_cloud.logic.TagCloudBlockEntryLogic");

			$url = (isset($_SERVER["REDIRECT_URL"])) ? $_SERVER["REDIRECT_URL"] : "";
			if(strpos($url, "page-")) $url = substr($url, 0, strpos($url, "/page-")) . "/";
			$url = rtrim($url, "/") . "/";

			$limit = PluginBlockUtil::getLimitByPageId($pageId, $soyId);
			if(is_null($limit)) $limit = 100000;

			$args = $logic->getArgs();
			$labelId = PluginBlockUtil::getLabelIdByPageId($pageId, $soyId);
			if(isset($args[0]) && strpos($args[0], "page-") === 0) $current = (int)str_replace("page-", "", $args[0]);
			$last_page_number = (int)ceil($logic->getTotal($labelId, $wordId) / $limit);
		}

		SOY2::import("site_include.plugin.soycms_search_block.component.BlockPluginPagerComponent");
		$obj->createAdd("s_pager", "BlockPluginPagerComponent", array(
			"list" => array(),
			"current" => $current,
			"last"	 => $last_page_number,
			"url"		=> $url,
			"queries" => array("tagcloud" => $wordId),
			"soy2prefix" => "p_block",
		));

		$obj->addModel("s_has_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number > 1)
		));
		$obj->addModel("s_no_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number < 2)
		));

		$obj->addLink("s_first_page", array(
			"soy2prefix" => "p_block",
			"link" => $url . "?tagcloud=" . $wordId,
		));

		$obj->addLink("s_last_page", array(
			"soy2prefix" => "p_block",
			"link" => ($last_page_number > 1) ? $url . "page-" . ($last_page_number - 1) . "?tagcloud=" . $wordId : null,
		));

		$obj->addLabel("s_current_page", array(
			"soy2prefix" => "p_block",
			"text" => max(1, $current + 1),
		));

		$obj->addLabel("s_pages", array(
			"soy2prefix" => "p_block",
			"text" => $last_page_number,
		));

		$obj->addModel("s_has_next_page", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number > 1 && ($current + 1) < $last_page_number)
		));

		$obj->addLink("s_next_page", array(
			"soy2prefix" => "p_block",
			"link" => $url . "page-" . ($current + 1) . "?tagcloud=" . $wordId,
		));

		$obj->addModel("s_has_prev_page", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number > 1 && $current > 0)
		));

		$obj->addLink("s_prev_page", array(
			"soy2prefix" => "p_block",
			"link" => $url . "page-" . ($current - 1) . "?tagcloud=" . $wordId,
		));
	}

	function onPageTitleFormat($args){
		$fmt = $args["format"];
		if(!strlen($fmt) || is_bool(strpos($fmt, "%TAG_CLOUD%"))) return $fmt;
		
		$wordId = self::_getWordIdFromGetParam();
		$tag = ((is_string($wordId) && strlen($wordId)) || (is_numeric($wordId) && $wordId > 0)) ? htmlspecialchars((string)self::_getTagByWordId($wordId), ENT_QUOTES, "UTF-8") : "";
		return str_replace("%TAG_CLOUD%", $tag, $fmt);
	}

	private function _getWordIdFromGetParam(){
		if(!isset($_GET["tagcloud"])) return null;
		if(is_numeric($_GET["tagcloud"])){
			return (int)$_GET["tagcloud"];
		//ハッシュ値の場合
		}else{
			SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudDictionaryDAO");
			try{
				return SOY2DAOFactory::create("TagCloudDictionaryDAO")->getByHash($_GET["tagcloud"])->getId();
			}catch(Exception $e){
				//
			}
		}
		return null;
	}

	/**
	 * @param int|string
	 * @return string
	 */
	private function _getTagByWordId($wordId){
		SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudDictionaryDAO");
		try{
			if(is_numeric($wordId)){
				return SOY2DAOFactory::create("TagCloudDictionaryDAO")->getById($wordId)->getWord();
			}else{	//ハッシュ値の場合
				return SOY2DAOFactory::create("TagCloudDictionaryDAO")->getByHash($wordId)->getWord();
			}
		}catch(Exception $e){
			//
		}
		return null;
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$_exist = $dao->executeQuery("SELECT * FROM TagCloudDictionary", array());
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
		if(is_null($obj)) $obj = new TagCloudPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
