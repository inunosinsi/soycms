<?php
/**
 * WordPress側の仕様：https://wpdocs.osdn.jp/%E3%83%87%E3%83%BC%E3%82%BF%E3%83%99%E3%83%BC%E3%82%B9%E6%A7%8B%E9%80%A0
 */
class Wp2SoyLogic extends SOY2LogicBase {

	const MODE_WP = 0;
	const MODE_SOY = 1;
	const POST_LIMIT = 500;

	//post_status
	private $status_list = array(
		"publish",	//公開済
		"future",	//予約済
		"draft",	//下書き
		"pending",	//承認待ち
		"private", 	//非公開
		"trash",	//ゴミ箱
		"auto_draft",	//自動保存
		"auto-draft",
		"inherit"		//継承
	);

	//記事インポート時にWPのデータベースから取得しないステータスのリスト
	private $exclude_status_list = array(
		"trash", "auto_draft", "auto-draft", "inherit"
	);

	private $pdo;
	private $soyPdo;

	function __construct(){}

	function execute(){
		$this->pdo = self::_pdo();
		if(is_null($this->pdo)) return false;

		$this->soyPdo = self::_pdo(self::MODE_SOY);
		if(is_null($this->soyPdo)) return false;

		//カテゴリの移行 wp_temrs → Label
		self::_importLabels();

		//記事の移行 wp_posts → Entry
		self::_importEntries();

		self::_importRelations();

		//接続を閉じる
		$this->pdo = null;
		$this->soyPdo = null;
	}

	// WPのカテゴリをSOYのラベルとして登録
	private function _importLabels(){
		$terms = self::_getTerms();
		if(!count($terms)) return false;
		
		//taxonomyを取得する
		$taxonomies = self::_getTaxonomies();
		
		$labelHashList = self::_getLabelHashList();
		
		$this->soyPdo->beginTransaction();
		$stmt = $this->soyPdo->prepare("INSERT INTO Label(caption, alias, description) VALUES(:caption, :alias, :description)");
		foreach($terms as $term){
			if($term["slug"] == "uncategorized") continue;
			$caption = trim($term["name"]);
			if(!strlen($caption) || is_numeric(array_search(md5($caption), $labelHashList))) continue;
			
			try{
				$stmt->execute(array(
					":caption" => $caption, 
					":alias" => $caption, 
					":description" => (isset($taxonomies[$term["term_id"]])) ? $taxonomies[$term["term_id"]] : ""
				));
			}catch(Exception $e){
				//
			}
		}
		$this->soyPdo->commit();

		return true;
	}

	private function _getTerms(){
		$stmt = $this->pdo->prepare("SELECT * FROM wp_terms;");
		$successed = $stmt->execute();
		return ($successed) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
	}

	private function _getTaxonomies(){
		$stmt = $this->pdo->prepare("SELECT * FROM wp_term_taxonomy;");
		$successed = $stmt->execute();
		if(!$successed) return array();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(!count($results)) return array();
		
		$list = array();
		foreach($results as $res){
			if(!isset($res["term_id"]) || !is_numeric($res["term_id"])) continue;

			// @ToDo taxonomyでcategory以外の値はあるか？

			if(!isset($res["description"]) || !strlen($res["description"])) continue;

			$list[(int)$res["term_id"]] = trim($res["description"]);
		}
		return $list;
	}

	private function _importEntries(){
		$stmt = $this->pdo->prepare("SELECT COUNT(ID) AS CNT FROM wp_posts WHERE post_type != 'page' AND post_status NOT IN (\"" . implode("\",\"", $this->exclude_status_list) . "\");");
		$successed = $stmt->execute();
		if(!$successed) return false;

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(!isset($res[0]["CNT"])) return false;

		$total = (int)$res[0]["CNT"];
		if($total === 0) return false;

		$aliasHashList = self::_getAliasHashList();

		$i = 0;
		for(;;){
			$stmt = $this->pdo->prepare(
				"SELECT * FROM wp_posts ".
				"WHERE post_type != 'page' ".
				"AND post_status NOT IN (\"" . implode("\",\"", $this->exclude_status_list) . "\") ".
				"LIMIT " . self::POST_LIMIT . " ".
				"OFFSET " . (self::POST_LIMIT * $i++)
			);
			$successed = $stmt->execute();
			if(!$successed) break;

			$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if(!count($posts)) break;

			$this->soyPdo->beginTransaction();
			$stmt = $this->soyPdo->prepare(
				"INSERT INTO ".
				"Entry(title, alias, content, cdate, udate, openPeriodStart, openPeriodEnd, isPublished) ".
				"VALUES(:title, :alias, :content, :cdate, :udate, :openPeriodStart, :openPeriodEnd, :isPublished)"
			);
			foreach($posts as $post){
				//3番目の記事まではサンプル記事なので除外する
				if(!isset($post["ID"]) || !is_numeric($post["ID"]) || (int)$post["ID"] <= 3) continue;

				//statusが自動下書き(auto-draft)、inherit(継承)かtrash(ゴミ箱)の場合は移行の対象外
				$status = $post["post_status"];
				if($status == "auto-draft" || $status == "inherit" || $status == "trash") continue;

				if($post["post_type"] == "page") continue;

				$title = trim($post["post_title"]);

				//既に登録済みの記事であればスルー
				if(is_numeric(array_search(md5($title), $aliasHashList))) continue;
				
				$content = trim($post["post_content"]);
				$excerpt = trim($post["post_excerpt"]);	// @ToDo post_excerpt 記事の抜粋はどうすべきか？
				if(!strlen($title) && !strlen($content)) continue;

				/**
				 * post_status
				 * 公開済(publish)
				 * 予約済 (future)	? @ToDo 公開開始日や終了日をどうしよう
				 * 下書き (draft)
				 * 承認待ち (pending)
				 * 非公開 (private)
				 */
				switch($status){
					case "publish":
					case "future":
						$isPublished = Entry::ENTRY_ACTIVE;
						break;
					case "draft":
					case "pending":
					case "private":
						$isPublished = Entry::ENTRY_NOTPUBLIC;
						break;
				}

				try{
					$stmt->execute(array(
						":title" => $title, 
						":alias" => $title,
						":content" => $content,
						":cdate" => strtotime($post["post_date"]),
						":udate" => strtotime($post["post_modified"]),
						":openPeriodStart" => 0,
						":openPeriodEnd" => 2147483647,
						":isPublished" => $isPublished
					));
				}catch(Exception $e){
					//
				}
			}
			$this->soyPdo->commit();
		}
	}

	/**
	 * @return array(object_id => array(label_id(SOYCMS)...))
	 */
	private function _getTermRelationships(){
		$stmt = $this->pdo->prepare(
			"SELECT rel.object_id, tax.term_id FROM wp_term_relationships rel ".
			"INNER JOIN wp_term_taxonomy tax ".
			"ON rel.term_taxonomy_id = tax.term_taxonomy_id;"
		);
		$successed = $stmt->execute();
		if(!$successed) return array();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(!count($results)) return array();

		$table = self::_getCorrespondenceTable();

		$list = array();
		foreach($results as $res){
			//object_idが3まではサンプルなので除外する
			if((int)$res["object_id"] <= 3) continue;
			if(!isset($table[$res["term_id"]])) continue;

			if(!isset($list[(int)$res["object_id"]])) $list[(int)$res["object_id"]] = array();
			$list[(int)$res["object_id"]][] = $table[$res["term_id"]];
		}

		return $list;
	}

	private function _importRelations(){
		$relations = self::_getTermRelationships();

		$aliasHashList = self::_getAliasHashList();

		$i = 0;
		for(;;){
			$stmt = $this->pdo->prepare(
				"SELECT ID, post_title FROM wp_posts ".
				"WHERE post_type != 'page' ".
				"AND post_status NOT IN (\"" . implode("\",\"", $this->exclude_status_list) . "\") ".
				"LIMIT " . self::POST_LIMIT . " ".
				"OFFSET " . (self::POST_LIMIT * $i++)
			);
			$successed = $stmt->execute();
			if(!$successed) break;

			$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if(!count($posts)) break;

			$this->soyPdo->beginTransaction();
			$stmt = $this->soyPdo->prepare("INSERT INTO EntryLabel(entry_id, label_id) VALUES(:entry_id, :label_id)");
			foreach($posts as $post){
				if(!isset($relations[$post["ID"]]) || !count($relations[$post["ID"]])) continue;

				$entryId = array_search(md5($post["post_title"]), $aliasHashList);
				if(!is_numeric($entryId)) continue;

				foreach($relations[$post["ID"]] as $labelId){
					try{
						$stmt->execute(array(
							":entry_id" => $entryId, 
							":label_id" => $labelId
						));
					}catch(Exception $e){
						//
					}
				}
			}
			$this->soyPdo->commit();
		}
	}

	/**
	 * wp_terms(WordPress)とLabel(SOY CMS)の対応表
	 */
	private function _getCorrespondenceTable(){
		$stmt = $this->pdo->prepare("SELECT term_id, name FROM wp_terms WHERE slug != 'uncategorized';");
		$successed = $stmt->execute();
		if(!$successed) return array();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(!count($results)) return array();

		$labelHashList = self::_getLabelHashList();
		$table = array();
		foreach($results as $res){
			$labelId = array_search(md5($res["name"]), $labelHashList);
			if(!is_numeric($labelId)) continue;
			$table[$res["term_id"]] = (int)$labelId;
		}
		return $table;
	}
	
	private function _getLabelHashList(){
		$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		if(!count($labels)) return array();

		$list = array();
		foreach($labels as $label){
			if(!is_numeric($label->getId())) continue;
			$list[(int)$label->getId()] = md5($label->getCaption());
		}
		return $list;
	}

	/**
	 * 記事のエイリアスをハッシュ化したリスト
	 */
	private function _getAliasHashList(){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id, alias FROM Entry");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$l = array();
		foreach($res as $v){
			$l[(int)$v["id"]] = md5($v["alias"]);
		}
		return $l;
	}

	/**
	 * @param int
	 * @return PDO|null
	 */
	private function _pdo(int $mode=self::MODE_WP){
		switch($mode){
			case self::MODE_WP:
				SOY2::import("site_include.plugin.wordpress_import_entry.util.WordPressImportEntryUtil");
				$cnf = WordPressImportEntryUtil::getConfig();
				try{
					return new PDO("mysql:dbname=".$cnf["name"].";charset=utf8;host=".$cnf["host"], $cnf["user"], $cnf["password"]);
				}catch(Exception $e){
					return null;
				}
			case self::MODE_SOY:
				try{
					return new PDO(SOY2DAOConfig::Dsn(), SOY2DAOConfig::user(), SOY2DAOConfig::pass());
				}catch(Exception $e){
					return null;
				}
		}
	}
}
