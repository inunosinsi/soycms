<?php
SOY2::import("logic.site.Entry.class.LabeledEntry");

/**
 * @entity LabeledEntry
 */
abstract class LabeledEntryDAO extends SOY2DAO{
	const ORDER_ASC = 1;
	const ORDER_DESC = 2;

	private $blockClass;
	private $sort;	//0:cdate or 1:udate

	function setBlockClass($blockClass){
		$this->blockClass =$blockClass;
	}
	function setSort($sort){
		$this->sort = $sort;
	}

	/**
	 * @index id
	 * @order EntryLabel.display_order ,Entry.id
	 * @distinct
	 */
	abstract function getByLabelId($labelId);

	/**
	 * @index id
	 * @order EntryLabel.display_order, Entry.cdate desc, Entry.id desc
	 * @distinct
	 * @query EntryLabel.label_id in (<?php implode(',',:labelids) ?>)
	 * @group Entry.id
	 * @having count(Entry.id) = <?php count(:labelids) ?>
	 */
	abstract function getByLabelIds($labelids);

	/**
	 * getByLabelIdsだと重すぎる場所があったので追加
	 */
	function getByLabelIdsOnlyId(array $labelIds, bool $orderReverse=false, int $limit=-1, int $offset=-1){
		$sql = "SELECT entry.* FROM Entry entry ";
		$sql .= "WHERE entry.id IN (SELECT entry_id FROM EntryLabel WHERE label_id IN (" .implode(",", $labelIds) . ") GROUP BY entry_id HAVING count(*) = " . count($labelIds) . ") ";

		$sql .= self::_addOrder($labelIds, $orderReverse);
		
		if($limit >= 0) $sql .= " LIMIT " . $limit;
		if($offset >= 0) $sql .= " OFFSET " . $offset;	/** @ToDo 作成日時順に並べて高速化 **/
		
		try{
			$results = $this->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}

		if(!count($results)) return array();

		$list = array();
		foreach($results as $row){
			$list[$row["id"]] = soycms_set_entry_object($this->getObject($row));
		}

		return $list;
	}

	function countByLabelIdsOnlyId(array $labelIds){
		$sql = "SELECT count(DISTINCT entry.id) AS COUNT FROM Entry entry ".
				"INNER JOIN EntryLabel label ".
				"ON entry.id = label.entry_id ";

		$sql .= "WHERE entry.id IN (SELECT entry_id FROM EntryLabel WHERE label_id IN (" .implode(",", $labelIds) . ") GROUP BY entry_id HAVING count(*) = " . count($labelIds) . ") ";

		try{
			$results = $this->executeQuery($sql);
		}catch(Exception $e){
			return 0;
		}

		return (isset($results[0]["COUNT"])) ? (int)$results[0]["COUNT"] : 0;
	}

	/**
	 * @order max_udate desc
	 * @distinct
	 * @columns EntryLabel.label_id as label_id, max(Entry.udate) as max_udate
	 * @group EntryLabel.label_id
	 * @return array
	 */
	abstract function getRecentLabelIds();

	/**
	 * getOpenEntrybyLabeIdへのエイリアス
	 */
	function getOpenEntryByLabelId($labelId, int $now, bool $orderReverse=false, int $limit=0, int $offset=0){
		return $this->getOpenEntryByLabelIds(array($labelId), $now, Entry::PERIOD_START, Entry::PERIOD_END, $orderReverse, $limit, $offset);
	}

	/**
	 * @order Entry.udate desc
	 */
	abstract function get();

	/**
	 * ブログページ用。
	 * 公開しているエントリーをラベルでフィルタリングして取得。（絞込み）
	 *
	 * @final
	 */
	function getOpenEntryByLabelIds(array $labelIds, int $now, int $start=Entry::PERIOD_START, $end=Entry::PERIOD_END, bool $orderReverse=false, int $limit=-1, int $offset=-1){
		return 	$this->getOpenEntryByLabelIdsImplements($labelIds, $now, true, $start, $end, $orderReverse, $limit, $offset);
	}

	/**
	 * ブログページ用。
	 * ラベルの絞り込みをアンドとオアを切り替える
	 * ORのときの表示順は保証できない（？）
	 */
	function getOpenEntryByLabelIdsImplements(array $labelIds, int $now, bool $isAnd, int $start=Entry::PERIOD_START, int $end=Entry::PERIOD_END, bool $orderReverse=false, int $limit=-1, int $offset=-1){
		$sql = "SELECT entry.* FROM Entry entry ";
		$binds = array();
		$where = array();

		//null、空文字や0を削除
		if(count($labelIds)) $labelIds = array_diff($labelIds, array(null));
		if(count($labelIds)) $labelIds = array_diff($labelIds, array(0));
		
		//数値のみ
		$labelIds = array_map(function($val) {return (int)$val; }, $labelIds);
		if(count($labelIds)){
			//ブログページ等
			if($isAnd){
				$where[] = "entry.id IN (SELECT entry_id FROM EntryLabel WHERE label_id IN (" .implode(",", $labelIds) . ") GROUP BY entry_id HAVING count(*) = " . count($labelIds) . ")";
			//ブログリンクブロック等
			}else{
				$where[] = "entry.id IN (SELECT entry_id FROM EntryLabel WHERE label_id IN (" .implode(",", $labelIds) . "))";
			}
		}

		if(!defined("CMS_PREVIEW_ALL")){
			$where[] = "entry.isPublished = 1";
			$where[] = "(entry.openPeriodEnd >= :now AND entry.openPeriodStart < :now)";
			$binds[":now"] = $now;
		}

		//endに等号は付けない
		$where[] = "(entry.cdate BETWEEN :start AND :end)";
		$binds[":start"] = $start;
		$binds[":end"] = $end-1;

		if(count($where)){
			$sql .= "WHERE " . implode(" AND ", $where);
		}

		$sql .= self::_addOrder($labelIds, $orderReverse);
		
		if($limit > 0) {
			$sql .= " LIMIT " . $limit;
			if($offset > 0) {
				$sql .= " OFFSET " . $offset;	/** @ToDo 作成日時順に並べて高速化 **/
			}
		}

		$dao = new SOY2DAO();	//LabeledEntryDAOだと前の実行の影響を受けるため、都度DAOを呼び出す

		try{
			$results = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}

		unset($dao);	//念の為、都度破棄

		if(!count($results)) return array();
		$list = array();
		$cnt = 0;
		foreach($results as $row){
			if(!isset($row["id"]) || !is_numeric($row["id"])) continue;
			$list[$row["id"]] = soycms_set_entry_object($this->getObject($row));
		}

		return $list;
	}

	function countOpenEntryByLabelIds(array $labelIds, int $now, bool $isAnd, int $start=Entry::PERIOD_START, int $end=Entry::PERIOD_END){
		$sql = "SELECT count(DISTINCT entry.id) AS COUNT FROM Entry entry ".
				"INNER JOIN EntryLabel label ".
				"ON entry.id = label.entry_id ";
		$binds = array();
		$where = array();

		//null、空文字や0を削除
		if(count($labelIds)) $labelIds = array_diff($labelIds, array(null));
		if(count($labelIds)) $labelIds = array_diff($labelIds, array(0));

		//数値のみ
		$labelIds = array_map(function($val) {return (int)$val; }, $labelIds);
		if(count($labelIds)){
			//ブログページ等
			if($isAnd){
				$where[] = "entry.id IN (SELECT entry_id FROM EntryLabel WHERE label_id IN (" .implode(",", $labelIds) . ") GROUP BY entry_id HAVING count(*) = " . count($labelIds) . ")";
			//ブログリンクブロック等
			}else{
				$where[] = "entry.id IN (SELECT entry_id FROM EntryLabel WHERE label_id IN (" .implode(",", $labelIds) . "))";
			}
		}
		

		if(!defined("CMS_PREVIEW_ALL")){
			$where[] = "entry.isPublished = 1";
			$where[] = "(entry.openPeriodEnd >= :now AND entry.openPeriodStart < :now)";
			$binds[":now"] = $now;
		}

		//endに等号は付けない
		$where[] = "(entry.cdate BETWEEN :start AND :end)";
		$binds[":start"] = $start;
		$binds[":end"] = $end-1;
		
		if(count($where)){
			$sql .= "WHERE " . implode(" AND ", $where);
		}

		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["COUNT"])) ? (int)$res[0]["COUNT"] : 0;
	}

	/**
	 * ブログページ用。
	 * 公開しているエントリーをラベルでフィルタリングして数え上げる
	 *
	 * @index id
	 * @columns Entry.id
	 * @distinct
	 * @query EntryLabel.label_id in (<?php implode(',',:labelids) ?>) AND Entry.isPublished = 1 AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)
	 * @group Entry.id
	 * @having count(Entry.id) = <?php count(:labelids) ?>
	 * @return array
	 */
	abstract function getOpenEntryCountByLabelIds($labelids,$now);

	/**
	 * ブログページ用。
	 * 公開しているエントリーをラベルでフィルタリングして数え上げる
	 *
	 * @columns EntryLabel.label_id, COUNT(*)
	 * @query EntryLabel.label_id in (<?php implode(',',:labelids) ?>) AND Entry.isPublished = 1 AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)
	 * @group EntryLabel.label_id
	 * @return array
	 */
	// abstract function getOpenEntryCountListByLabelIds($labelids,$now);

	/**
	 * @final
	 */
	function getOpenEntryCountListByLabelIds(array $labelIds, int $now){
		if(!count($labelIds)) return array();
		
		try{
			$res = $this->executeQuery(
				"SELECT label.label_id, COUNT(*) AS COUNT FROM EntryLabel label ".
				"INNER JOIN Entry entry ".
				"ON label.entry_id = entry.id ".
				"WHERE label.label_id IN (".implode(",", $labelIds).") ".
				"AND entry.isPublished = 1 ".
				"AND (entry.openPeriodEnd > :now AND entry.openPeriodStart <= :now) ".
				"GROUP BY label.label_id"
			, array(":now" => $now));
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["label_id"]] = (int)$v["COUNT"];
		}
		
		return $list;
	}

	//ソート
	private function _addOrder(array $labelIds=array(), bool $orderReverse=false){
		switch((int)$this->sort){
			case 1:	//1:udate
				$sort = "udate";
				break;
			case 0:	//0:cdate
			default:	//0:cdate
				$sort = "cdate";
		}
		$order = ($orderReverse) ? "ASC" : "DESC";

		$blockClass = $this->blockClass;
		if(is_null($blockClass) && defined("SOYCMS_BLOG_PAGE_MODE") && SOYCMS_BLOG_PAGE_MODE == "_category_"){
			$blockClass = "BlogCategoryComponent";
		}

		switch((string)$blockClass){
			case "BlogCategoryComponent":
				// ブログページで設定されているカテゴリを除く
				$blogLabelId = (int)soycms_get_blog_page_object($_SERVER["SOYCMS_PAGE_ID"])->getPageConfigObject()->blogLabelId;
				$categoryLabelId = 0;
				foreach($labelIds as $labelId){
					if($labelId == $blogLabelId) continue;
					$categoryLabelId = $labelId;
					break;
				}
			
				return " Order By (SELECT display_order FROM EntryLabel WHERE label_id = " . $categoryLabelId . " AND entry_id = entry.id), entry.".$sort." " . $order . ", entry.id " . $order;
			//記事毎の表示順が使えるブロックはラベルブロックのみ
			case "LabeledBlockComponent":
			case "AdminPageComponent":
				$labelId = (count($labelIds) >= 1) ? $labelIds[count($labelIds)-1] : 0;	// 設定に不備がないか？を念の為に確認しておく
				if($labelId > 0){
					return " Order By (SELECT display_order FROM EntryLabel WHERE label_id = " . $labelId . " AND entry_id = entry.id), entry.".$sort." " . $order . ", entry.id " . $order;
				}
				// pass through					
			default:
				return " Order By entry.".$sort." " . $order . ", entry.id " . $order;
		}
	}

	/**
	 * @final
	 * ブログページ用
	 * 公開しているエントリーの次のエントリーを取得する（次＝管理画面の表示順で上）
	 */
	function getNextOpenEntry(int $labelId, LabeledEntry $entry, int $now){
		if(is_null($entry->getDisplayOrder())){
			$entry = clone($entry);
			$entry->setMaxDisplayOrder();
		}
		return $this->getNextOpenEntryImpl($labelId, $entry, $now);
	}

	/**
	 * 公開しているエントリーの次のエントリーを取得する
	 * @query EntryLabel.label_id = :labelId AND ( EntryLabel.display_order < :displayOrder OR EntryLabel.display_order = :displayOrder AND ( Entry.cdate > :cdate OR Entry.cdate = :cdate AND Entry.id > :id ) ) AND (Entry.isPublished = 1 AND Entry.openPeriodStart <= :now AND :now < Entry.openPeriodEnd)
	 * @order EntryLabel.display_order desc, Entry.cdate asc,Entry.id asc
	 * @return object
	 */
	abstract function getNextOpenEntryImpl($labelId, $entry, $now);

	/**
	 * @final
	 * ブログページ用
	 * 公開しているエントリーの前のエントリーを取得する（前＝管理画面の表示順で下）
	 */
	function getPrevOpenEntry(int $labelId, LabeledEntry $entry, int $now){
		if(is_null($entry->getDisplayOrder())){
			$entry = clone($entry);
			$entry->setMaxDisplayOrder();
		}
		return $this->getPrevOpenEntryImpl($labelId, $entry, $now);
	}

	/**
	 * 公開しているエントリーの前のエントリーを取得する
	 * @query EntryLabel.label_id = :labelId AND ( EntryLabel.display_order > :displayorder OR EntryLabel.display_order = :displayorder AND ( Entry.cdate < :cdate OR Entry.cdate = :cdate AND Entry.id < :id ) ) AND (Entry.isPublished = 1 AND Entry.openPeriodStart <= :now AND :now < Entry.openPeriodEnd)
	 * @order EntryLabel.display_order asc, Entry.cdate desc, Entry.id desc
	 * @return object
	 */
	abstract function getPrevOpenEntryImpl($labelId, $entry, $now);

	/**
	 * 月毎のエントリー数を数え上げる
	 */
	function getCountMonth(array $labelIds){

		$labelIds = array_map(function($val) { return (int)$val; }, $labelIds);

		$binds = array(":now"=>SOYCMS_NOW);


		$spanSQL = 'SELECT max(cdate) as max, min(cdate) as min ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)';

		$result = $this->executeQuery($spanSQL,$binds);

		$max = (isset($result[0]['max'])) ? $result[0]['max'] : Entry::PERIOD_END;
		$min = (isset($result[0]['min'])) ? $result[0]['min'] : Entry::PERIOD_START;

		$maxMonth = date('m',$max);
		$maxYear = date('Y',$max);
		$minMonth = date('m',$min);
		$minYear = date('Y',$min);

		$ret_val = array();
		$countSQL =
				'SELECT count(Entry.id) as total ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)' .
				'AND (Entry.cdate BETWEEN :begin AND :end)';

		for($y = $minYear; $y <= $maxYear; $y++){
			$span_min = ($y == $minYear)?$minMonth:1;
			$span_max = ($y == $maxYear)?$maxMonth:12;


			for($m = $span_min;  $m<=$span_max; $m++){
				$begin = mktime(0,0,0,$m,1,$y);
				$end   = mktime(0,0,0,$m+1,1,$y);

				$result = $this->executeQuery($countSQL,array(
					":begin"=>$begin,
					":end"=>$end-1,
					":now"=>SOYCMS_NOW
				));

				$ret_val[mktime (1, 1, 1, $m, 1, $y)] = (isset($result[0]['total'])) ? $result[0]['total'] : 0;
			}

		}

		//降順に並び替え
		$ret_val = array_reverse($ret_val,true);

		return $ret_val;
	}

	function getMonth(array $labelIds){
		$labelIds = array_map(function($val) { return (int)$val; }, $labelIds);

		$binds = array(":now"=>SOYCMS_NOW);


		$spanSQL = 'SELECT max(cdate) as max, min(cdate) as min ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)';

		$result = $this->executeQuery($spanSQL,$binds);

		$max = (isset($result[0]['max'])) ? $result[0]['max'] : Entry::PERIOD_END;
		$min = (isset($result[0]['min'])) ? $result[0]['min'] : Entry::PERIOD_START;

		$maxMonth = date('m',$max);
		$maxYear = date('Y',$max);
		$minMonth = date('m',$min);
		$minYear = date('Y',$min);

		$ret_val = array();

		for($y = $minYear; $y <= $maxYear; $y++){
			$span_min = ($y == $minYear)?$minMonth:1;
			$span_max = ($y == $maxYear)?$maxMonth:12;


			for($m = $span_min;  $m<=$span_max; $m++){
				$ret_val[mktime (1, 1, 1, $m, 1, $y)] = 1;
			}
		}

		//降順に並び替え
		$ret_val = array_reverse($ret_val,true);

		return $ret_val;
	}

	/**
	 * 年毎のエントリー数を数え上げる
	 */
	function getCountYear(array $labelIds){

		$labelIds = array_map(function($val) { return (int)$val; }, $labelIds);

		$binds = array(":now"=>SOYCMS_NOW);


		$spanSQL = 'SELECT max(cdate) as max, min(cdate) as min ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)';

		$result = $this->executeQuery($spanSQL,$binds);

		$max = (isset($result[0]['max'])) ? $result[0]['max'] : Entry::PERIOD_END;
		$min = (isset($result[0]['min'])) ? $result[0]['min'] : Entry::PERIOD_START;

		$maxYear = date('Y',$max);
		$minYear = date('Y',$min);

		$ret_val = array();
		$countSQL =
				'SELECT count(Entry.id) as total ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)' .
				'AND (Entry.cdate BETWEEN :begin AND :end)';

		for($y = $minYear; $y <= $maxYear; $y++){
			$begin = mktime(0,0,0,1,1,$y);
			$end   = mktime(0,0,0,1,1,$y+1);

			$result = $this->executeQuery($countSQL,array(
				":begin"=>$begin,
				":end"=>$end-1,
				":now"=>SOYCMS_NOW
			));

			$ret_val[mktime (1, 1, 1, 1, 1, $y)] = (isset($result[0]['total'])) ? $result[0]['total'] : 0;
		}

		//降順に並び替え
		$ret_val = array_reverse($ret_val,true);

		return $ret_val;

	}


	/**
	 * @table Entry left outer join EntryLabel on(Entry.id = EntryLabel.entry_id)
	 * @columns Entry.*
	 * @query Entry.isPublished <> 1
	 * @order Entry.udate desc, Entry.id desc
	 * @distinct
	 */
	abstract function getClosedEntries();

	/**
	 * @table Entry left outer join EntryLabel on(Entry.id = EntryLabel.entry_id)
	 * @columns Entry.*
	 * @query Entry.openPeriodStart >= :now OR Entry.openPeriodEnd < :now
	 * @order Entry.udate desc, Entry.id desc
	 * @distinct
	 */
	abstract function getOutOfDateEntries($now);

	/**
	 * @query EntryLabel.label_id is null
	 * @table Entry left outer join EntryLabel on(Entry.id = EntryLabel.entry_id)
	 * @order Entry.udate desc, Entry.id desc
	 */
	abstract function getNoLabelEntries();

	/**
	 * @query EntryLabel.label_id = :labelId
	 * @order Entry.udate desc, Entry.id desc
	 * @distinct
	 */
	abstract function getRecentEntriesByLabelId($labelId);
}
