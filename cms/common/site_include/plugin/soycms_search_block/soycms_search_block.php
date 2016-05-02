<?php
SOYCMS_Search_Block_Plugin::registerPlugin();

class SOYCMS_Search_Block_Plugin{
	
	const PLUGIN_ID = "soycms_search_block";
	
	
	function getId(){
		return SOYCMS_Search_Block_Plugin::PLUGIN_ID;
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
			"version"=>"0.5"
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
        
        $pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

        //ブログページか調べる
        $template = "";
        try{
            $blog = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId);
            $uri = str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $_SERVER["PATH_INFO"]);

            //トップページ
            if($uri === (string)$blog->getTopPageUri()){
                $template = $blog->getTopTemplate();
                //アーカイブページ		
            }else if(strpos($uri, $blog->getCategoryPageUri()) === 0 || strpos($uri, $blog->getMonthPageUri()) === 0){
                $template = $blog->getArchiveTemplate();
                //記事ごとページ
            }else{
                $template = $blog->getEntryTemplate();
            }
        }catch(Exception $e){
            try{
                $template = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getTemplate();
            }catch(Exception $e){
                return array();
            }
        }

        try{
            $blocks = SOY2DAOFactory::create("cms.BlockDAO")->getByPageId($pageId);
        }catch(Exception $e){
            return array();
        }

        if(!count($blocks)) return array();

        $block = null;
        foreach($blocks as $obj){
            if($obj->getClass() == "PluginBlockComponent"){
                $block = $obj;
            }
        }

        if(is_null($block)) return array();

        //ラベルIDを取得とデータベースから記事の取得件数指定
        $labelId = null;
        $count = null;
        if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
            if(preg_match('/cms:label=\"(.*?)\"/', $tmp[1], $ltmp)){
                if(isset($ltmp[1]) && is_numeric($ltmp[1])) $labelId = (int)$ltmp[1];
            }
            if(preg_match('/cms:count=\"(.*?)\"/', $tmp[1], $ctmp)){
                if(isset($ctmp[1]) && is_numeric($ctmp[1])) $count = (int)$ctmp[1];
            }
        }else{
            return array();
        }

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
				
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_Search_Block_Plugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_Search_Block_Plugin();
		}
		CMSPlugin::addPlugin(SOYCMS_Search_Block_Plugin::PLUGIN_ID,array($obj,"init"));
	}
}
?>