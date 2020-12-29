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
}
