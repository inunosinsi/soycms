<?php
SOYCMS_Random_Block_Plugin::registerPlugin();

class SOYCMS_Random_Block_Plugin{

	const PLUGIN_ID = "soycms_random_block";


	function getId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 初期化
	 */
	function init(){

		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY CMS記事ランダム表示ブロックプラグイン",
			"description"=>"プラグインブロックで記事をランダムに表示します",
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
		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");

        $pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

        //ラベルIDを取得とデータベースから記事の取得件数指定
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
        $count = PluginBlockUtil::getLimitByPageId($pageId);

        $entryDao = SOY2DAOFactory::create("cms.EntryDAO");
        $sql = "SELECT ent.* FROM Entry ent ".
             "JOIN EntryLabel lab ".
             "ON ent.id = lab.entry_id ".
             "WHERE ent.openPeriodStart < " . time() . " ".
             "AND ent.openPeriodEnd >= " .time() . " ".
             "AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ";
        $binds = array();

        //ラベルIDを指定する場合
        if(isset($labelId)){
            $sql .= "AND lab.label_id = :labelId ";
            $binds[":labelId"] = $labelId;
        }

        $sql .= "GROUP BY ent.id ";

        if(SOY2DAOConfig::type() == "mysql"){
            $sql .= "ORDER BY Rand() ";
        }else{
            $sql .= "ORDER BY Random() ";
        }

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
	 * 設定画面の表示
	 */
	function config_page($message){
        SOY2::import("site_include.plugin.soycms_random_block.config.RandomBlockConfigPage");
        $form = SOY2HTMLFactory::createInstance("RandomBlockConfigPage");
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
			$obj = new SOYCMS_Random_Block_Plugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
