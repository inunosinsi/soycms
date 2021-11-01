<?php
/**
 * WordPress側の仕様：https://wpdocs.osdn.jp/%E3%83%87%E3%83%BC%E3%82%BF%E3%83%99%E3%83%BC%E3%82%B9%E6%A7%8B%E9%80%A0
 */
class Wp2SoyLogic extends SOY2LogicBase {

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

	function __construct(){}

	function execute(){
		$this->pdo = self::_pdo();
		if(is_null($this->pdo)) return false;

		//カテゴリの移行 wp_temrs → Label
		self::_importLabels();

		//記事の移行 wp_posts → Entry
		self::_importEntries();

		//接続を閉じる
		$this->pdo = null;
	}

	// WPのカテゴリをSOYのラベルとして登録
	private function _importLabels(){
		$terms = self::_getTerms();
		if(!count($terms)) return false;

		//taxonomyを取得する
		$taxonomies = self::_getTaxonomies();

		$dao = SOY2DAOFactory::create("cms.LabelDAO");

		foreach($terms as $term){
			if($term["slug"] == "uncategorized") continue;
			$caption = trim($term["name"]);
			try{
				$label = $dao->getByCaption($caption);
				continue;
			}catch(Exception $e){
				//
			}

			//ラベルとして登録する
			$label = new Label();
			$label->setCaption($caption);
			if(isset($taxonomies[$term["term_id"]])) $label->setDescription($taxonomies[$term["term_id"]]);

			try{
				$dao->insert($label);
			}catch(Exception $e){

			}
		}

		return true;
	}

	private function _getTerms(){
		$stmt = $this->pdo->prepare("SELECT * FROM wp_terms;");
		$successed = $stmt->execute();
		if(!$successed) return array();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

		$relations = self::_getTermRelationships();

		$dao = SOY2DAOFactory::create("cms.EntryDAO");
		$entryLogic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		$entryLabelDao = SOY2DAOFactory::create("cms.EntryLabelDAO");

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

			foreach($posts as $post){
				//3番目の記事まではサンプル記事なので除外する
				if(!isset($post["ID"]) || !is_numeric($post["ID"]) || (int)$post["ID"] <= 3) continue;

				//statusが自動下書き(auto-draft)、inherit(継承)かtrash(ゴミ箱)の場合は移行の対象外
				$status = $post["post_status"];
				if($status == "auto-draft" || $status == "inherit" || $status == "trash") continue;

				if($post["post_type"] == "page") continue;

				$title = trim($post["post_title"]);
				//既に登録済みの記事であればスルー
				try{
					$entryId = $dao->getByAlias($title)->getId();
					if(is_numeric($entryId)) continue;
				}catch(Exception $e){
					//
				}

				$content = trim($post["post_content"]);
				$excerpt = trim($post["post_excerpt"]);	// @ToDo post_excerpt 記事の抜粋はどうすべきか？
				if(!strlen($title) && !strlen($content)) continue;

				$entry = new Entry();
				$entry->setTitle($title);
				$entry->setContent($content);
				$entry->setCdate(strtotime($post["post_date"]));
				$entry->setUdate(strtotime($post["post_modified"]));

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
						$entry->setIsPublished(Entry::ENTRY_ACTIVE);
						break;
					case "draft":
					case "pending":
					case "private":
						$entry->setIsPublished(Entry::ENTRY_NOTPUBLIC);
						break;
				}

				$entryId = $entryLogic->create($entry);
				if(!is_numeric($entryId) || (int)$entryId === 0) continue;

				if(!isset($relations[$post["ID"]]) || !count($relations[$post["ID"]])) continue;

				// @ToDo ラベル
				foreach($relations[$post["ID"]] as $labelId){
					$entryLabelObj = new EntryLabel();
					$entryLabelObj->setEntryId($entryId);
					$entryLabelObj->setLabelId($labelId);
					try{
						$entryLabelDao->insert($entryLabelObj);
					}catch(Exception $e){
						//
					}
				}
			}
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

	/**
	 * wp_terms(WordPress)とLabel(SOY CMS)の対応表
	 */
	private function _getCorrespondenceTable(){
		$stmt = $this->pdo->prepare("SELECT term_id, name FROM wp_terms WHERE slug != 'uncategorized';");
		$successed = $stmt->execute();
		if(!$successed) return array();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(!count($results)) return array();

		$dao = SOY2DAOFactory::create("cms.LabelDAO");
		$table = array();
		foreach($results as $res){
			try{
				$labelId = $dao->getByCaption($res["name"])->getId();
			}catch(Exception $e){
				continue;
			}
			if(!is_numeric($labelId)) continue;
			$table[$res["term_id"]] = $labelId;
		}
		return $table;
	}

	private function _pdo(){
		SOY2::import("site_include.plugin.wordpress_import_entry.util.WordPressImportEntryUtil");
		$cnf = WordPressImportEntryUtil::getConfig();
		try{
			return new PDO("mysql:dbname=" . $cnf["name"] . ";charset=utf8;host=" . $cnf["host"], $cnf["user"], $cnf["password"]);
		}catch(Exception $e){
			return null;
		}
	}
}
