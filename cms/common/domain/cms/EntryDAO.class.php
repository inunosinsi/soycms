<?php

/**
 * @entity cms.Entry
 */
abstract class EntryDAO extends SOY2DAO{

	const DATE_MIN = 0;
	const DATE_MAX = 2147483647;

	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(Entry $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(Entry $bean);

	abstract function delete($id);

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @return row
	 * @columns *
	 */
	abstract function getArrayById($id);

	/**
	 * @order id
	 * @return object
	 */
	abstract function getByAlias($alias);
	abstract function getsByTitle($title);

	/**
	 * @query ##id## = :id AND Entry.isPublished = 1 AND (openPeriodEnd > :now AND openPeriodStart <= :now)
	 * @return object
	 */
	abstract function getOpenEntryById($id,$now);

	/**
	 * @query ##alias## = :alias AND Entry.isPublished = 1 AND (openPeriodEnd > :now AND openPeriodStart <= :now)
	 * @return object
	 */
	abstract function getOpenEntryByAlias($alias,$now);

	/**
	 * @order id desc
	 */
	abstract function get();

	function setPublish($id,$publish){
		$entity = $this->getById($id);
		$entity->setIsPublished($publish);
		return $this->update($entity);
	}

	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		$i = 0;

		//記事表示の高速化
		for(;;){
			try{
				$res = $this->executeQuery("SELECT id FROM Entry WHERE cdate = :cdate LIMIT 1;", array(":cdate" => $binds[":cdate"] + $i));
			}catch(Exception $e){
				$res = array();
			}

			if(!count($res)) break;
			$i++;
		}
		$binds[":cdate"] += $i;

		//プラグインによっては読み込まれていないことがある
		if(!class_exists("UserInfoUtil")) SOY2::import("util.UserInfoUtil");
		if(!isset($binds[':author'])) $binds[':author'] = UserInfoUtil::getUserName();

		// 作成日にあり得ない数字が入り、CPUの負荷がかかったことがある
		if(!isset($binds[":cdate"]) || !is_numeric($binds[":cdate"])) $binds[":cdate"] = time();
		if($binds[":cdate"] < self::DATE_MIN || $binds[":cdate"] > self::DATE_MAX) $binds[":cdate"] = time();

		if(!isset($binds[':udate'])) $binds[':udate'] = time();

		if(!isset($binds[":openPeriodStart"])) $binds[":openPeriodStart"] = self::DATE_MIN;
		if(!isset($binds[":openPeriodEnd"])) $binds[":openPeriodEnd"] = self::DATE_MAX;

		return array($query,$binds);
	}

	/**
	 * 最新エントリーを取得
	 * @order udate desc, id desc
	 */
	abstract function getRecentEntries();

	/**
	 * 公開中かつ公開期間内の記事で最も早く公開期間外になる記事
	 * @columns min(openPeriodEnd) as openPeriodEndMin
	 * @query Entry.isPublished = 1 AND (openPeriodEnd > :now AND openPeriodStart <= :now)
	 * @return column_openPeriodEndMin
	 */
	abstract function getNearestClosingEntry($now);

	/**
	 * 公開中かつ公開期間外の記事で最も早く公開期間内になる記事
	 * @columns min(openPeriodStart) as openPeriodStartMin
	 * @query Entry.isPublished = 1 AND (openPeriodStart > :now)
	 * @return column_openPeriodStartMin
	 */
	abstract function getNearestOpeningEntry($now);

	/**
	 * @final
	 * @paran int, int, int
	 * @return Entry
	 */
	function getOpenEntryByIdAndBlogLabelId(int $entryId,int $blogLabelId, int $now){
		try{
			$res = $this->executeQuery(
				"SELECT ent.* FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE ent.id = :entry_id ".
				"AND ent.isPublished = 1 ".
				"AND (ent.openPeriodEnd > :start AND ent.openPeriodStart <= :end) ".
				"AND lab.label_id = :label_id ".
				"LIMIT 1",
				array(":entry_id" => $entryId, ":label_id" => $blogLabelId, ":start" => $now, ":end" => $now)
			);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0])) ? $this->getObject($res[0]) : new Entry();
	}

	/**
	 * @final
	 * @param string, int, int
	 * @return Entry
	 */
	function getOpenEntryByAliasAndBlogLabelId(string $alias,int $blogLabelId, int $now){
		try{
			$res = $this->executeQuery(
				"SELECT ent.* FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE ent.alias = :alias ".
				"AND ent.isPublished = 1 ".
				"AND (ent.openPeriodEnd > :start AND ent.openPeriodStart <= :end) ".
				"AND lab.label_id = :label_id ".
				"LIMIT 1",
				array(":alias" => $alias, ":label_id" => $blogLabelId, ":start" => $now, ":end" => $now)
			);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0])) ? $this->getObject($res[0]) : new Entry();
	}
}
