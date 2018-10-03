<?php

TableOfContentsPlugin::register();

class TableOfContentsPlugin{

	const PLUGIN_ID = "TableOfContents";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"見出し自動生成プラグイン",
			"description"=>"投稿されたテキストから見出しを作成する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.4"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		//active or non active
		//そもそもsetEventはonActive以外activeじゃないと無視されるのでactiveCheckは不要
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
			CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
			CMSPlugin::setEvent('onOutput', self::PLUGIN_ID, array($this,"onOutput"));
		} else {
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];
		if(!isset($_SERVER["SOYCMS_PAGE_ID"])) return $html;

		try{
			$blog = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($_SERVER["SOYCMS_PAGE_ID"]);
		}catch(Exception $e){
			$blog = new BlogPage();
		}

		$heading = "";
		if(!is_null($blog->getId()) && strpos($_SERVER["REQUEST_URI"], "/" . $blog->getEntryPageUri() . "/") !== false) {
			//ブログのエイリアスを取得
			$alias = trim(substr($_SERVER["PATH_INFO"], strrpos($_SERVER["PATH_INFO"], "/")), "/");
			$sql = "SELECT attr.entry_value FROM EntryAttribute attr ".
					"INNER JOIN Entry ent ".
					"ON attr.entry_id = ent.id ".
					"WHERE ent.alias = :alias ".
					"AND attr.entry_field_id = :fieldId";
			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery($sql, array(":alias" => $alias, ":fieldId" => self::PLUGIN_ID));
			}catch(Exception $e){
				$res = array();
			}

			if(isset($res[0]["entry_value"]) && strlen($res[0]["entry_value"])) {
				$array = soy2_unserialize($res[0]["entry_value"]);
				$logic = SOY2Logic::createInstance("site_include.plugin.table_of_contents.logic.CreateHeadingLogic");
				$heading = $logic->createHeading($array);

				$list = $logic->getHeadingList();
				if(count($list)){
					foreach($list as $href => $title){
						$title = htmlspecialchars($title, ENT_QUOTES, "UTF-8");
						preg_match('/<h[0-9].*?>' . self::addEscapeChar($title) . '<\/h[0-9]>/', $html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) continue;
						$hTag = str_replace(">" . $title, " id=\"" . $href . "\">" . $title, $tmp[0]);
						$html = str_replace($tmp[0], $hTag, $html);
					}
				}
			}
		}

		return str_replace("##HEADING##", $heading, $html);
	}

	private function addEscapeChar($str){
		foreach(array("(", ")", "?") as $c){
			$str = str_replace($c, "\\" . $c, $str);
		}
		return $str;
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.table_of_contents.config.TableOfContentsFormPage");
		$form = SOY2HTMLFactory::createInstance("TableOfContentsFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @TODO 記事画面からの削除
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];

		//コンテンツからビルドする
		$content = $entry->getContent() . $entry->getMore();

		//データの保存用
		$array = array();

		preg_match('/<h([0-9]).*?>/', $content, $tmp);
		if(isset($tmp[1]) && is_numeric($tmp[1])){
			$array = self::createTitleTree($content, (int)$tmp[1]);
		}

		$attrDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		if(count($array)){
			try{
				$attr = $attrDao->get($entry->getId(), self::PLUGIN_ID);
			}catch(Exception $e){
				$attr = new EntryAttribute();
				$attr->setEntryId($entry->getId());
				$attr->setFieldId(self::PLUGIN_ID);
			}

			$attr->setValue(soy2_serialize($array));

			try{
				$attrDao->insert($attr);
			}catch(Exception $e){
				try{
					$attrDao->update($attr);
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}else{
			try{
				$attrDao->delete($entry->getId(), self::PLUGIN_ID);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

	/**
	 * @params content(本文), h(階層)
	 */
	private function createTitleTree($content, $h = 1){
		preg_match_all('/<h' . $h . '.*?>.*?<\/h' . $h . '.*?>/', $content, $res);
		$array = array();
		if(isset($res[0]) && count($res[0])){
			foreach($res[0] as $r){
				$values = array();

				//下記の正規表現は絶対に成功する
				preg_match('/<h' . $h . '.*?>(.*?)<\/h' . $h . '.*?>/', $r, $rr);
				$values["title"] = trim($rr[1]);	//この処理は必要ないかも

				$conts = array();	//本文をばらしていく
				$content .= "<h" . $h . ">***</h" . $h . ">";
				for(;;){
					preg_match('/<h' . $h . '.*?>.*?<\/h' . $h . '.*?>[\s\S]*?<h' . $h . '.*?>/', $content, $tmp);
					if(!isset($tmp[0])) break;
					$c = trim(substr($tmp[0], 0, strrpos($tmp[0], "<h" . $h)));
					$content = trim(str_replace($c, "", $content));
					$conts[] = $c;
				}

				if(count($conts)){
					foreach($conts as $cont){
						preg_match('/<h' . $h . '.*?>(.*?)<\/h' . $h . '.*?>/', $cont, $tmp);
						if(preg_match('/<h[0-9].*?>\*\*\*<\/h[0-9]/', $cont, $temp)) continue;
						$values = array();

						//titleの上書き
						$values["title"] = trim($tmp[1]);

						$tag = trim($tmp[0]);

						$cont = trim(str_replace($tag, "", $cont));
						preg_match('/h[0-9]/', $cont, $a);
						if(count($a)){
							$t = self::createTitleTree($cont, $h + 1);
							if(isset($t) && is_array($t) && count($t)) $values["children"] = $t;
						}
						$array[] = $values;
					}
				}
			}
		}
		return $array;
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

		//カスタムフィールドアドバンスドのテーブルを活用する
		$file = file_get_contents(dirname(dirname(__FILE__)) . "/CustomFieldAdvanced/sql/init_".SOYCMS_DB_TYPE.".sql");
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

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new TableOfContentsPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
