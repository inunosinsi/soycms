<?php

CustomSearchFieldPlugin::register();

class CustomSearchFieldPlugin{

	const PLUGIN_ID = "CustomSearchField";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "カスタムサーチフィールド",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"0.9"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//管理側
			if(!defined("_SITE_ROOT_")){
				self::_ajust();

				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));

			//公開側
			}else{
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
			}

			CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));

		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$values = SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic")->getByEntryId($entryId);

		$configs = CustomSearchFieldUtil::getConfig();
		if(isset($configs) && count($configs)){
			foreach($configs as $key => $field){

				$csfValue = (isset($values[$key])) ? $values[$key] : null;
				if(isset($csfValue) && $field["type"] == CustomSearchFieldUtil::TYPE_TEXTAREA){
					$csfValue = nl2br($csfValue);
				}

				$csfValueLength = strlen(trim(strip_tags($csfValue)));

				$htmlObj->addModel($key . "_visible", array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"visible" => ($csfValueLength > 0)
				));

				$htmlObj->addModel($key . "_is_not_empty", array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"visible" => ($csfValueLength > 0)
				));

				$htmlObj->addModel($key . "_is_empty", array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"visible" => ($csfValueLength === 0)
				));

				$htmlObj->addLabel($key, array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"html" => (isset($csfValue)) ? $csfValue : null
				));

				$htmlObj->addLabel($key . "_link", array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"link" => (isset($csfValue) && strlen($csfValue)) ? $csfValue : null
				));

				switch($field["type"]){
					case CustomSearchFieldUtil::TYPE_CHECKBOX:
						if(strlen($field["option"])){
							$vals = explode(",", $csfValue);
							$opts = explode("\n", $field["option"]);
							foreach($opts as $i => $opt){
								$opt = trim($opt);
								$htmlObj->addModel($key . "_"  . $i . "_visible", array(
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
									"visible" => (in_array($opt, $vals))
								));

								$htmlObj->addLabel($key . "_" . $i, array(
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
									"text" => $opt
								));
							}
						}
						break;
				}
			}
		}
	}

	function onPageOutput($obj){
		//ブログページでトップページ以外では以下のコードを読み込まない
		if(get_class($obj) == "CMSBlogPage" && ($obj->mode == "_entry_" || $obj->mode == "_month_" || $obj->mode == "_category_")) return;

		SOY2::import("site_include.plugin.soycms_search_block.component.BlockPluginPagerComponent");
		$logic = SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.SearchLogic");

		$url = (isset($_SERVER["REDIRECT_URL"])) ? $_SERVER["REDIRECT_URL"] : "";
		if(strpos($url, "page-")) $url = substr($url, 0, strpos($url, "/page-")) . "/";

		//末尾にスラッシュがない場合はスラッシュを付ける
		$url = rtrim($url, "/") . "/";

		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$limit = PluginBlockUtil::getLimitByPageId($pageId);
		if(is_null($limit)) $limit = 100000;

		$args = $logic->getArgs();
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
		$current = (isset($args[0]) && strpos($args[0], "page-") === 0) ? (int)str_replace("page-", "", $args[0]) : 0;
		$last_page_number = (int)ceil($logic->getTotal($labelId) / $limit);

		$obj->createAdd("s_pager", "BlockPluginPagerComponent", array(
			"list" => array(),
			"current" => $current,
			"last"	 => $last_page_number,
			"url"		=> $url,
			//"queries" => array("q" => $query),
			"soy2prefix" => "p_block",
		));

		$obj->addModel("s_has_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number >1)
		));
		$obj->addModel("s_no_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number <2)
		));

		$obj->addLink("s_first_page", array(
			"soy2prefix" => "p_block",
			"link" => $url,
		));

		$obj->addLink("s_last_page", array(
			"soy2prefix" => "p_block",
			"link" => ($last_page_number > 0) ? $url . "page-" . ($last_page_number - 1) : null,
		));

		$obj->addLabel("s_current_page", array(
			"soy2prefix" => "p_block",
			"text" => max(1, $current + 1),
		));

		$obj->addLabel("s_pages", array(
			"soy2prefix" => "p_block",
			"text" => $last_page_number,
		));
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		if(isset($_POST["custom_search"])){
			$arg = SOY2PageController::getArguments();
			$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
			SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic")->save($entryId, $_POST["custom_search"]);
        }

		return true;
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		return true;
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return self::buildFormOnEntryPage($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return self::buildFormOnEntryPage($entryId);
	}

	private function buildFormOnEntryPage($entryId){
		$values = SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic")->getByEntryId($entryId);
		$html = array();

		SOY2::import("site_include.plugin.CustomSearchField.component.FieldFormComponent");
        foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
			$html[] = "<div class=\"form-group\">";
            $html[] = "<label>" . htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8") . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</label>";
            $value = (isset($values[$key])) ? $values[$key] : null;
            $html[] = FieldFormComponent::buildForm($key, $field, $value);
            $html[] = "</div>";
        }

        return implode("\n", $html);
	}

	function onLoad(){
		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

		//ラベルIDを取得とデータベースから記事の取得件数指定
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
		if(is_null($labelId)) return array();

		$count = PluginBlockUtil::getLimitByPageId($pageId);
		return SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.SearchLogic")->search($labelId, $count);
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
			$exist = $dao->executeQuery("SELECT * FROM EntrySearchAttribute", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/CREATE/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = trim("CREATE" . $sql);
			try{
				$dao->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	private function _ajust(){
		//EntryCustomSearchで最新記事分のデータはあるか？
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT entry_id FROM EntryCustomSearch ORDER BY entry_id DESC LIMIT 1;");
		}catch(Exception $e){
			$res = array();
		}

		$lastEntryId = (isset($res[0]["entry_id"])) ? (int)$res[0]["entry_id"] : 0;

		//最新の記事IDよりも上のIDがあるか調べる
		try{
			$res = $dao->executeQuery("SELECT id FROM Entry WHERE id > :entryId", array(":entryId" => $lastEntryId));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return;

		foreach($res as $v){
			$sql = "INSERT INTO EntryCustomSearch (entry_id) VALUES (" . $v["id"] . ")";
			try{
				$dao->executeQuery($sql);
			}catch(Exception $e){
				//
			}
		}
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.CustomSearchField.config.CustomSearchFieldConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomSearchFieldConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new CustomSearchFieldPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
