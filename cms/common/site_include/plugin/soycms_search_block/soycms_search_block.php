<?php
SOYCMS_Search_Block_Plugin::registerPlugin();

class SOYCMS_Search_Block_Plugin{

	const PLUGIN_ID = "soycms_search_block";


	function getId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 初期化
	 */
	function init(){

		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY CMS検索結果ブロックプラグイン",
			"description"=>"プラグインブロックでブログ記事の検索結果を表示します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.7"
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

        //検索クエリが空文字の場合は検索をやめる
        if(!isset($_GET["q"]) || strlen(trim($_GET["q"])) === 0) return array();
        $query = htmlspecialchars(trim($_GET["q"]), ENT_QUOTES, "UTF-8");

				//検索結果ブロックプラグインのUTILクラスを利用する
				SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
        $pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

        //ラベルIDを取得とデータベースから記事の取得件数指定
        $labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
				var_dump($labelId);
        $count = PluginBlockUtil::getLimitByPageId($pageId);

        //ラベルIDの指定がない場合は空の配列を返す
        if(is_null($labelId)) return array();

        $sql = "SELECT * FROM Entry entry ".
             "INNER JOIN EntryLabel label ".
             "ON entry.id = label.entry_id ".
             "WHERE label.label_id = :label_id ".
             "AND (entry.title LIKE :query OR entry.content LIKE :query OR entry.more LIKE :query) ".
             "AND entry.isPublished = 1 ".
             "AND entry.openPeriodEnd >= :now ".
             "AND entry.openPeriodStart < :now ".
             "ORDER BY entry.cdate desc ";

        if(isset($count) && $count > 0){
            $sql .= "LIMIT " . $count;
        }

        $binds = array(
			":label_id" => $labelId,
			":query" => "%" . $query . "%",
			":now" => time()
        );

        $dao = SOY2DAOFactory::create("cms.EntryDAO");

        try{
            $results = $dao->executeQuery($sql, $binds);
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

    function returnPluginId(){
        return self::PLUGIN_ID;
    }


	/**
	 * 設定画面の表示
	 */
	function config_page($message){
        SOY2::import("site_include.plugin.soycms_search_block.config.SearchBlockConfigPage");
        $form = SOY2HTMLFactory::createInstance("SearchBlockConfigPage");
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
			$obj = new SOYCMS_Search_Block_Plugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
