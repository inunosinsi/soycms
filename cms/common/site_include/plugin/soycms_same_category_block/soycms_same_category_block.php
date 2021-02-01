<?php
SOYCMSSameCategoryBlockPlugin::registerPlugin();

class SOYCMSSameCategoryBlockPlugin{

	const PLUGIN_ID = "same_category_block";

	function getId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 初期化
	 */
	function init(){

		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY CMS同一カテゴリーの記事一覧ブロックプラグイン",
			"description"=>"プラグインブロックで同一カテゴリの記事一覧を表示します。記事詳細ページでのみ動作します。",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.8"
		));

	    if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));

			CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
	    }
	}

  function onLoad(){

		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");

		//詳細ページでない場合は空の配列を返す
		if(!self::_checkIsBlogEntryPage($pageId)) return array();

		//記事詳細からカテゴリの設定を習得する
		$labelIds = self::_getLabelIds($pageId);
		if(is_null($labelIds)) return array();

    	//ラベルIDを取得とデータベースから記事の取得件数指定
		$count = PluginBlockUtil::getLimitByPageId($pageId);

		//並び順の指定
		$randomMode = PluginBlockUtil::getSortRandomMode($pageId);

    	$sql = "SELECT ent.* FROM Entry ent ".
        	"INNER JOIN EntryLabel lab ".
        	"ON ent.id = lab.entry_id ".
        	"WHERE lab.label_id IN (" . implode(",", $labelIds) . ") ".
        	"AND ent.isPublished = 1 ".
        	"AND ent.openPeriodEnd >= :now ".
        	"AND ent.openPeriodStart < :now ";

		//記事のランダム表示
		if($randomMode){
			if(SOY2DAOConfig::type() == "mysql"){
				$sql .= "ORDER BY Rand() ";
			}else{
				$sql .= "ORDER BY Random() ";
			}
		}else{
			$sql .= "ORDER BY ent.cdate desc ";
		}

		if(isset($count) && $count > 0){
			 $sql .= "LIMIT " . $count;
		}

		$dao = SOY2DAOFactory::create("cms.EntryDAO");

	    try{
	        $results = $dao->executeQuery($sql, array(":now" => time()));
	    }catch(Exception $e){
	        return array();
	    }
		if(!count($results)) return array();

		$soycms_search_result = array();
		foreach($results as $key => $row){
			if(isset($row["id"]) && (int)$row["id"]){
				$soycms_search_result[$row["id"]] = $dao->getObject($row);
			}
    	}
		return $soycms_search_result;
	}

	//詳細ページを開いているか？
	private function _checkIsBlogEntryPage($pageId){
		$page = PluginBlockUtil::getBlogPageByPageId($pageId);
		if(is_null($page->getId())) return false;

		$uri = (strlen($page->getUri())) ? "/" . $page->getUri() : "";
		if(isset($_SERVER["PATH_INFO"])){
			return (is_numeric(strpos($_SERVER["PATH_INFO"], $uri . "/" . $page->getEntryPageUri() . "/")));
		}else{
			return (is_numeric(strpos($_SERVER["REQUEST_URI"], $uri . "/" . $page->getEntryPageUri() . "/")));
		}
	}

	private function _getLabelIds($pageId){
		if(isset($_SERVER["PATH_INFO"])){
			$alias = trim(substr($_SERVER["PATH_INFO"], strrpos($_SERVER["PATH_INFO"], "/") + 1), "/");
		}else{	//xampp対策でPATH_INFOではなく、REQUEST_URIを使う
			$alias = trim(substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1), "/");
			//GETパラメータがある場合は除く
			if(is_numeric(strpos($alias, "?"))) $alias = substr($alias, 0, strpos($alias, "?"));
		}


		$sql = "SELECT ent.id, lab.label_id FROM Entry ent ".
						"INNER JOIN EntryLabel lab ".
						"ON ent.id = lab.entry_id ".
						"WHERE ent.alias = :alias";

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery($sql, array(":alias" => $alias));
		}catch(Exception $e){
			return null;
		}

		if(!count($res)) return null;

		//ブログで指定されているラベルIDは除く
		$blogLabelId = (int)PluginBlockUtil::getBlogPageByPageId($pageId)->getBlogLabelId();

		$list = array();

		//ラベルの指定
		$labelIds = PluginBlockUtil::getLabelIdsByPageId($pageId);

		foreach($res as $v){
			if(isset($v["label_id"]) && is_numeric($v["label_id"]) && $blogLabelId !== (int)$v["label_id"]){
				//cms:labelsで条件を付ける
				if(count($labelIds)){
					if(in_array($v["label_id"], $labelIds)){
						$list[] = (int)$v["label_id"];
					}
				//labelIdsがない場合は無条件で入れる
				}else{
					$list[] = (int)$v["label_id"];
				}
			}
		}

		return $list;
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 設定画面の表示
	 */
	function config_page($message){
    	SOY2::import("site_include.plugin.soycms_same_category_block.config.SameCategoryBlockConfigPage");
    	$form = SOY2HTMLFactory::createInstance("SameCategoryBlockConfigPage");
    	$form->setPluginObj($this);
    	$form->execute();
    	return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMSSameCategoryBlockPlugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
