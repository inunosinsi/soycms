<?php
SOY2::import("domain.cms.Entry");
class EntryLogic extends SOY2LogicBase{

	var $offset;
	var $limit;
	var $reverse = false;//逆順にする（DisplayOrder以外のcdate,idの部分のみ）
	var $totalCount;
	var $entryDAO;
	var $entryLabelDAO;
	var $labeledEntryDAO;

	function setLimit($limit){
		$this->limit = $limit;
	}

	function setOffset($offset){
		$this->offset  =$offset;
	}

	function setReverse($reverse){
		$this->reverse  =$reverse;
	}

	/**
	 * エントリーを新規作成
	 */
	function create($bean){

		$dao = $this->getEntryDAO();

		$bean->setContent($this->cleanupMCETags($bean->getContent()));
		$bean->setMore($this->cleanupMCETags($bean->getMore()));

		//数値以外（空文字列を含む）がcdateに入っていれば現在時刻を作成日時にする
		if(!is_numeric($bean->getCdate())){
			$bean->setCdate(SOYCMS_NOW);
		}

		if(UserInfoUtil::hasEntryPublisherRole() != true){
			$bean->setOpenPeriodEnd(CMSUtil::encodeDate(null,false));
			$bean->setOpenPeriodStart(CMSUtil::encodeDate(null,true));

			$bean->setIsPublished(false);
		}

		$id = $dao->insert($bean);

		if($bean->isEmptyAlias()){
			$bean->setId($id);//updateを実行するため
			$bean->setAlias($this->getUniqueAlias($id,$bean->getTitle()));
			$dao->update($bean);
		}

		return $id;
	}

	/**
	 * エントリーを更新
	 */
	function update($bean){

		$dao = $this->getEntryDAO();

		//数値以外（空文字列を含む）がcdateに入っていれば現在時刻を作成日時にする
		if(!is_numeric($bean->getCdate())){
			$bean->setCdate(SOYCMS_NOW);
		}

		if($bean->isEmptyAlias()){
			$bean->setAlias($this->getUniqueAlias($bean->getId(),$bean->getTitle()));
		}

		$bean->setContent($this->cleanupMCETags($bean->getContent()));
		$bean->setMore($this->cleanupMCETags($bean->getMore()));

		if(UserInfoUtil::hasEntryPublisherRole() != true){
			$old = $dao->getById($bean->getId());
			$bean->setOpenPeriodEnd(CMSUtil::encodeDate($old->getOpenPeriodEnd(),false));
			$bean->setOpenPeriodStart(CMSUtil::encodeDate($old->getOpenPeriodStart(),true));

			$bean->setIsPublished($old->getIsPublished());

		}else{
			$bean->setOpenPeriodEnd(CMSUtil::encodeDate($bean->getOpenPeriodEnd(),false));
			$bean->setOpenPeriodStart(CMSUtil::encodeDate($bean->getOpenPeriodStart(),true));
		}

		$dao->update($bean);

		return $bean->getId();
	}

	function deleteByIds($ids){
		$dao = $this->getEntryDAO();
		$entryLabelDao = $this->getEntryLabelDAO();
		$entryTrackbackDAO = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
		$entryCommentDAO = SOY2DAOFactory::create("cms.EntryCommentDAO");
		$entryHistoryLogic = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic");

		try{
			$dao->begin();

			foreach($ids as $id){
				$dao->delete($id);
				$entryLabelDao->deleteByEntryId($id);
				$entryHistoryLogic->onRemove($id);

				//@TODO トラックバックとコメントは削除しない方がいい？
				$entryTrackbackDAO->deleteByEntryId($id);
				$entryCommentDAO->deleteByEntryId($id);
			}
			$dao->commit();
			return true;
		}catch(Exception $e){
			$dao->rollback();
			return false;
		}
	}

	/**
	 * エントリーを1件取得
	 * 2008-10-29 内部使用のため、無限遠時刻の変換処理の追加
	 */
	function getById($id,$flag = true) {
		$dao = $this->getEntryDAO();
		$entry = $dao->getById($id);

		//無限遠時刻をnullになおす
		if($flag){
			$entry->setOpenPeriodEnd(CMSUtil::decodeDate($entry->getOpenPeriodEnd()));
			$entry->setOpenPeriodStart(CMSUtil::decodeDate($entry->getOpenPeriodStart()));
		}

		$entry->setLabels($this->getLabelIdsByEntryId($entry->getId()));

		return $entry;
	}

	/**
	 * 全て返す
	 */
	function get(){
		$dao = $this->getEntryDAO();

		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);
		$array = $dao->get();
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return $array;
	}

	/**
	 * ラベルの割り当てられたエントリーを全て返す
	 *
	 * 2007/12/21 getByLabelIdsのエイリアスとして定義
	 */
	function getByLabelId($labelid){
		return $this->getByLabelIds(array($labelid));
	}

	/**
	 * 非公開のエントリーを取得
	 */
	function getClosedEntryList(){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);

		$array = $dao->getClosedEntries();
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return $array;
	}

	/**
	 * 公開期間外のエントリー一覧を取得
	 */
	function getOutOfDateEntryList(){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);

		$array = $dao->getOutOfDateEntries(SOYCMS_NOW);
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return $array;
	}

	/**
	 * ラベルのついていないエントリー一覧を取得
	 */
	function getNoLabelEntryList(){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);

		$array = $dao->getNoLabelEntries();
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return $array;
	}


	/**
	 * ラベルを複数指定してエントリーをすべて取得
	 */
	function getByLabelIds($labelids,$flag = true, $start = null, $end = null){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);

		$array = $dao->getByLabelIdsOnlyId($labelids, $this->reverse);
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key] = $this->getById($key,false);
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}


		return $array;

	}

	/**
	 * エントリーに割り当てているラベルIDを全て取得
	 */
	function getLabelIdsByEntryId($entryId){
		$dao = $this->getEntryLabelDAO();

		$entryLabels = $dao->getByEntryId($entryId);
		$result = array();
		foreach($entryLabels as $obj){
			$result[] = $obj->getLabelId();
		}

		return $result;
	}

	function getLabeledEntryByEntryId($entryId){
		$dao = $this->getEntryLabelDAO();
		return $dao->getByEntryId($entryId);
	}

	/**
	 * 合計件数を返す
	 */
	function getTotalCount(){
		return $this->totalCount;
	}

	/**
	 * エントリーにラベルを割り当てる
	 */
	function setEntryLabel($entryId,$labelId){
		$dao = $this->getEntryLabelDAO();
		$dao->setByParams($entryId,$labelId);
	}

	/**
	 * エントリーについているラベルを全て削除
	 */
	function clearEntryLabel($entryId){
		$dao = $this->getEntryLabelDAO();
		$dao->deleteByEntryId($entryId);
	}

	/**
	 * エントリーからラベルを削除
	 */
	function unsetEntryLabel($entryId,$labelId){
		$dao = $this->getEntryLabelDAO();
		$dao->deleteByParams($entryId,$labelId);
	}


	/**
	 * 表示順の更新
	 */
	function updateDisplayOrder($entryId,$labelId,$displayOrder){
		$dao = $this->getEntryLabelDAO();
		$dao->deleteByParams($entryId,$labelId);
		$dao->setByParams($entryId,$labelId,$displayOrder);
	}

	/**
	 * ラベルとエントリーに対応する表示順を返す
	 */
	function getDisplayOrder($entryId,$labelId){
		$dao = $this->getEntryLabelDAO();
		try{
			return $dao->getByEntryIdLabelId($entryId,$labelId)->getDisplayOrder();
		}catch(Exception $e){
			return null;
		}

	}

	/**
	 * 表示期間を含めたラベル付けされたエントリーを取得
	 */
	function getOpenEntryByLabelId($labelId){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);
		$array = $dao->getOpenEntryByLabelId($labelId,SOYCMS_NOW,$this->reverse);
		$this->totalCount = $dao->getRowCount();
		return $array;
	}

	/**
	 * 表示期間を含めてラベル付けされたエントリーを取得（ラベルIDを複数指定）
	 */
	function getOpenEntryByLabelIds($labelIds,$isAnd = true, $start = null, $end = null){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);

		if($isAnd){
			//$labelIdsのラベルがすべて設定されている記事のみ取得
			$array = $dao->getOpenEntryByLabelIds($labelIds,SOYCMS_NOW,$start,$end,$this->reverse);
		}else{
			//$labelIdsのラベルがどれか１つでも設定されている記事を取得
			$array = $dao->getOpenEntryByLabelIdsImplements($labelIds,SOYCMS_NOW,false,$start,$end,$this->reverse);
		}
		foreach($array as $key => $entry){
			$array[$key]->setCommentCount($this->getApprovedCommentCountByEntryId($entry->getId()));
			$array[$key]->setTrackbackCount($this->getCertificatedTrackbackCountByEntryId($entry->getId()));
		}
		$this->totalCount = $dao->getRowCount();
		return $array;
	}

	/**
	 * ブログのエントリーを取得
	 */
	function getBlogEntry($blogLabelId,$entryId){
		$dao = $this->getEntryDAO();

		try{

			if(defined("CMS_PREVIEW_ALL")){
				if(is_numeric($entryId)){
					try{
						$entry = $dao->getById($entryId);
					}catch(Exception $e){
						$entry = $dao->getByAlias($entryId);
					}
				}else{
					$entry = $dao->getByAlias($entryId);
				}
			}else{
				if(is_numeric($entryId)){
					try{
						$entry = $dao->getOpenEntryById($entryId,SOYCMS_NOW);
					}catch(Exception $e){
						//記事IDで取得できなければ、エイリアスの方でも取得を試みる
						$entry = $dao->getOpenEntryByAlias($entryId,SOYCMS_NOW);
					}
				}else{
					$entry = $dao->getOpenEntryByAlias($entryId,SOYCMS_NOW);
				}
			}

			$entry = SOY2::cast("LabeledEntry",$entry);

			//ブログに所属しているエントリーかどうかチェックする
			$labelIds = $this->getLabelIdsByEntryId($entry->getId());
			if(!in_array($blogLabelId,$labelIds)){
				throw new Exception("This entry (id: {$entryId}) does not belong to the designated blog (label: {$blogLabelId}).");
			}

		}catch(Exception $e){
			//該当エントリーが見つからない場合は
			throw $e;
		}

		return $entry;
	}

	/**
	 * 次のエントリーを取得
	 */
	function getNextOpenEntry($blogLabelId,$entry){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit(1);

		try{
			$next = $dao->getNextOpenEntry($blogLabelId,$entry,SOYCMS_NOW);

		}catch(Exception $e){
			return new LabeledEntry();
		}

		return $next;
	}

	/**
	 * 前のエントリーを取得
	 */
	function getPrevOpenEntry($blogLabelId,$entry){
		$dao = $this->getLabeledEntryDAO();
		$dao->setLimit(1);

		try{
			$prev = $dao->getPrevOpenEntry($blogLabelId,$entry,SOYCMS_NOW);
		}catch(Exception $e){
			return new LabeledEntry();
		}

		return $prev;
	}

	/**
	 * 指定されたIDの公開状態をpublicに変更
	 */
	function setPublish($id,$publish){
		$dao = $this->getEntryDAO();
		if(is_array($id)){
			//配列だったらそれぞれを設定
			try{
				$dao->begin();
				foreach($id as $pId){
					$dao->setPublish($pId,$publish);
				}
				$dao->commit();
				return true;
			}catch(Exception $e){
				$dao->rollback();
				return false;
			}
		}else{
			//IDだったらそれを設定
			try{
				$dao->setPublish($id,$publish);
				return true;
			}catch(Exception $e){
				return false;
			}
		}
	}

	/**
	 * 月別アーカイブを数える
	 */
	function getCountMonth($labelIds){
		$dao = $this->getLabeledEntryDAO();
		return $dao->getCountMonth($labelIds);
	}

	/**
	 * 年別アーカイブを数える
	 */
	function getCountYear($labelIds){
		$dao = $this->getLabeledEntryDAO();
		return $dao->getCountYear($labelIds);
	}

	/**
	 * ラベルIDを複数指定し、公開しているエントリー数を数え上げる
	 */
	function getOpenEntryCountByLabelIds($labelIds){
		$dao = $this->getLabeledEntryDAO();
		$dao->getOpenEntryCountByLabelIds($labelIds,SOYCMS_NOW);
		$count = $dao->getRowCount();
		return $count;
	}

	/**
	 * ラベルID（複数）からエントリーを取得
	 */
	function getEntryByLabelIds($labelIds){
   		$dao = $this->getEntryDAO();

   		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);
		$array = $dao->getEntryByLabelIds($labelIds);
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return 	$array;
   	}

   	/**
   	 * 最近使用されたラベルを取得（管理側で使用）
   	 */
   	function getRecentLabelIds(){
   		$dao = $this->getLabeledEntryDAO();
   		$dao->setLimit($this->limit);
   		try{
   			$array = $dao->getRecentLabelIds();

   			$res = array();
   			foreach($array as $row){
   				$res[] = $row["label_id"];
   			}
   			$array = $res;
   		}catch(Exception $e){
   			$array = array();
   		}
   		return $array;
   	}

   	/**
   	 * 最近使用されたエントリーを取得（管理側で使用）
   	 */
   	function getRecentEntriesByLabelId($labelId){
   		$dao = $this->getLabeledEntryDAO();
   		$dao->setLimit($this->limit);
   		return $dao->getRecentEntriesByLabelId($labelId);
   	}

   	/**
   	 * 最近使用されたエントリーを取得（管理側で使用）
   	 */
   	function getRecentEntries(){
   		$dao = $this->getEntryDAO();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);
		$array = $dao->getRecentEntries();
		$this->totalCount = $dao->getRowCount();

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return $array;
   	}

   	/**
   	 * MCEの特殊なタグを取り除く
   	 * 空の<p></p>または<p />は<br />に変換
   	 */
   	function cleanupMCETags($html){
   		return  preg_replace('/<p><\/p>|<p\s+\/>/','<br />',preg_replace('/\s?mce_[a-zA-Z0-9_]+\s*=\s*"[^"]*"/','',$html));
   	}

   	/**
   	 * コメント数を取得
   	 */
   	function getCommentCount($entryId){
   		$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
   		return $dao->getCommentCountByEntryId($entryId);
   	}

   	function getApprovedCommentCountByEntryId($entryId){
   		$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
   		return $dao->getApprovedCommentCountByEntryId($entryId);
   	}

   	/**
   	 * トラックバック数を取得
   	 */
   	function getTrackbackCount($entryId){
   		$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
   		return $dao->getTrackbackCountByEntryId($entryId);
   	}

   	function getCertificatedTrackbackCountByEntryId($entryId){
   		$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");
   		return $dao->getCertificatedTrackbackCountByEntryId($entryId);
   	}

   	/**
   	 * getUniqueAlias
   	 * ユニークなエイリアスを取得
   	 */
   	function getUniqueAlias($id,$title){
   		$dao = $this->getEntryDAO();

   		//[?#\/%\&]は取り除く
   		//2009-02-17 CGIモードで不具合が出るので & も削除
   		//2009-02-17 Labelでも使うのでCMSUtil::sanitizeAliasに移動
   		$title = CMSUtil::sanitizeAlias($title);

   		//数字だけの場合は_を前につける
   		if(is_numeric($title)){
   			$title = "_".$title;
   		}

   		try{
   			$bean = $dao->getByAlias($title);

   			if($bean->getId() == $id){
   				return $title;
   			}
   		}catch(Exception $e){
   			//none
   			return $title;
   		}

   		return $title."_".$id;
   	}

   	function getEntryDAO(){
   		if(!$this->entryDAO){
   			$this->entryDAO = SOY2DAOFactory::create("cms.EntryDAO");
   		}
   		return $this->entryDAO;
   	}

   	function getEntryLabelDAO(){
   		if(!$this->entryLabelDAO){
   			$this->entryLabelDAO = SOY2DAOFactory::create("cms.EntryLabelDAO");
   		}
   		return $this->entryLabelDAO;
   	}

   	function getLabeledEntryDAO(){
   		if(!$this->labeledEntryDAO){
   			$this->labeledEntryDAO = SOY2DAOFactory::create("LabeledEntryDAO");
   		}
   		return $this->labeledEntryDAO;
   	}
}

/**
 * @table Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id)
 */
class LabeledEntry extends Entry{

	const ENTRY_ACTIVE = 1;
	const ENTRY_OUTOFDATE = -1;
	const ENTRY_NOTPUBLIC = -2;
	const ORDER_MAX = 10000000;

	/**
	 * @column label_id
	 */
	private $labelId;

	/**
	 * @column display_order
	 */
	private $displayOrder;

	/**
	 * @no_persistent
	 */
	private $labels;

	/**
	 * @no_persistent
	 */
	private $trackbackCount;

	/**
	 * @no_persistent
	 */
	private $commentCount;

   	function getLabelId() {
   		return $this->labelId;
   	}
   	function setLabelId($labelId) {
   		$this->labelId = $labelId;
   	}
   	function getDisplayOrder() {
   		return $this->displayOrder;
   	}
   	function setDisplayOrder($displayOrder) {
   		if(((int)$displayOrder) >= LabeledEntry::ORDER_MAX)return;
   		$this->displayOrder = $displayOrder;
   	}
   	function setMaxDisplayOrder(){
   		$this->displayOrder = LabeledEntry::ORDER_MAX;
   	}
   	function getLabels() {
   		return $this->labels;
   	}
   	function setLabels($labels) {
   		$this->labels = $labels;
   	}

   	function getTrackbackCount() {
   		return $this->trackbackCount;
   	}
   	function setTrackbackCount($trackbackCount) {
   		$this->trackbackCount = $trackbackCount;
   	}
   	function getCommentCount() {
   		return $this->commentCount;
   	}
   	function setCommentCount($commentCount) {
   		$this->commentCount = $commentCount;
   	}
}

/**
 * @entity LabeledEntry
 */
abstract class LabeledEntryDAO extends SOY2DAO{
	const ORDER_ASC = 1;
	const ORDER_DESC = 2;

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
	 *
	 * @index Entry.id
	 * @columns Entry.id,EntryLabel.display_order,Entry.cdate
	 * @order EntryLabel.display_order, Entry.cdate desc, Entry.id desc
	 * @distinct
	 * @group Entry.id,EntryLabel.display_order
	 * @having count(Entry.id) = <?php count(:labelids) ?>
	 * @query EntryLabel.label_id in (<?php implode(',',:labelids) ?>)
	 */
	function getByLabelIdsOnlyId($labelids, $orderReverse = false){
		$query = $this->getQuery();
		$binds = $this->getBinds();

		if($orderReverse){
			$query->setOrder(" EntryLabel.display_order, Entry.cdate asc, Entry.id asc ");
		}

		//MySQL5.7以降対策。groupingとhagingをnullにした
		$result = $this->executeQuery($query,$binds);

		$array = array();
		foreach($result as $row){
			$array[$row["id"]] = $this->getObject($row);
		}

		return $array;
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
	function getOpenEntryByLabelId($labelId,$now,$orderReverse = false){
		return $this->getOpenEntryByLabelIds(array($labelId),$now,null,null,$orderReverse);
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
	function getOpenEntryByLabelIds($labelIds,$now,$start = null, $end = null, $orderReverse = false){
		return 	$this->getOpenEntryByLabelIdsImplements($labelIds, $now, true, $start, $end, $orderReverse);
	}

	/**
	 * ブログページ用。
	 * ラベルの絞り込みをアンドとオアを切り替える
	 * ORのときの表示順は保証できない（？）
	 *
	 * @columns Entry.id,Entry.alias,Entry.title,Entry.content,Entry.more,Entry.cdate,Entry.udate,EntryLabel.display_order
	 * @order EntryLabel.display_order asc,Entry.cdate desc,Entry.id desc
	 * @distinct
	 * @group Entry.id,EntryLabel.display_order
	 * @having count(Entry.id) = <?php count(:labelIds) ?>
	 */
	function getOpenEntryByLabelIdsImplements($labelIds, $now, $isAnd, $start = null, $end = null, $orderReverse = false){
		$query = $this->getQuery();
		$query->where = "";

		if(is_array($labelIds)){
			//nullや空文字を削除
			if(count($labelIds)){
				$labelIds = array_diff($labelIds,array(null));
			}
			//数値のみ
			$labelIds = array_map(function($val) {return (int)$val; }, $labelIds);
			$query->where .= " EntryLabel.label_id in (" . implode(",",$labelIds) .") ";
		}else{
			//保険（ラベル指定なし）
			$query->where .= " true ";
		}

		$binds = array();

		if(!defined("CMS_PREVIEW_ALL")){
			$query->where .= "AND Entry.isPublished = 1 ";
			$query->where .= "AND (Entry.openPeriodEnd >= :now AND Entry.openPeriodStart < :now)";

			$binds[":now"] = $now;
		}

		if($isAnd == false)$query->having = "";



		if(strlen($start) && strlen($end)){
			//endに等号は付けない
			$query->where .= " AND (Entry.cdate >= :start AND Entry.cdate < :end)";

			$binds[":start"] = $start;
			$binds[":end"] = $end;
		}

		if($orderReverse){
			$query->setOrder(" EntryLabel.display_order, Entry.cdate asc, Entry.id asc ");
		}

		$result = $this->executeQuery($query,$binds);

		$array = array();
		foreach($result as $row){
			$array[$row["id"]] = $this->getObject($row);
		}

		return $array;
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
	 * @final
	 * ブログページ用
	 * 公開しているエントリーの次のエントリーを取得する（次＝管理画面の表示順で上）
	 */
	function getNextOpenEntry($labelId,$entry,$now){
		if(is_null($entry->getDisplayOrder())){
			$entry = clone($entry);
			$entry->setMaxDisplayOrder();
		}
		return $this->getNextOpenEntryImpl($labelId,$entry,$now);
	}

	/**
	 * 公開しているエントリーの次のエントリーを取得する
	 * @query EntryLabel.label_id = :labelId AND ( EntryLabel.display_order < :displayOrder OR EntryLabel.display_order = :displayOrder AND ( Entry.cdate > :cdate OR Entry.cdate = :cdate AND Entry.id > :id ) ) AND (Entry.isPublished = 1 AND Entry.openPeriodStart <= :now AND :now < Entry.openPeriodEnd)
	 * @order EntryLabel.display_order desc, Entry.cdate asc,Entry.id asc
	 * @return object
	 */
	abstract function getNextOpenEntryImpl($labelId,$entry,$now);

	/**
	 * @final
	 * ブログページ用
	 * 公開しているエントリーの前のエントリーを取得する（前＝管理画面の表示順で下）
	 */
	function getPrevOpenEntry($labelId,$entry,$now){
		if(is_null($entry->getDisplayOrder())){
			$entry = clone($entry);
			$entry->setMaxDisplayOrder();
		}
		return $this->getPrevOpenEntryImpl($labelId,$entry,$now);
	}

	/**
	 * 公開しているエントリーの前のエントリーを取得する
	 * @query EntryLabel.label_id = :labelId AND ( EntryLabel.display_order > :displayorder OR EntryLabel.display_order = :displayorder AND ( Entry.cdate < :cdate OR Entry.cdate = :cdate AND Entry.id < :id ) ) AND (Entry.isPublished = 1 AND Entry.openPeriodStart <= :now AND :now < Entry.openPeriodEnd)
	 * @order EntryLabel.display_order asc, Entry.cdate desc, Entry.id desc
	 * @return object
	 */
	abstract function getPrevOpenEntryImpl($labelId,$entry,$now);

	/**
	 * 月毎のエントリー数を数え上げる
	 */
	function getCountMonth($labelIds){

		$labelIds = array_map(function($val) { return (int)$val; }, $labelIds);

		$binds = array(":now"=>SOYCMS_NOW);


		$spanSQL = 'SELECT max(cdate) as max, min(cdate) as min ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)';

		$result = $this->executeQuery($spanSQL,$binds);

		$max = $result[0]['max'];
		$min = $result[0]['min'];

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
				'AND (Entry.cdate >= :begin AND Entry.cdate < :end)';

		for($y = $minYear; $y <= $maxYear; $y++){
			$span_min = ($y == $minYear)?$minMonth:1;
			$span_max = ($y == $maxYear)?$maxMonth:12;


			for($m = $span_min;  $m<=$span_max; $m++){
				$begin = mktime(0,0,0,$m,1,$y);
				$end   = mktime(0,0,0,$m+1,1,$y);

				$result = $this->executeQuery($countSQL,array(
					":begin"=>$begin,
					":end"=>$end,
					":now"=>SOYCMS_NOW
				));

				$ret_val[mktime (1, 1, 1, $m, 1, $y)] = $result[0]['total'];
			}

		}

		//降順に並び替え
		$ret_val = array_reverse($ret_val,true);

		return $ret_val;
	}

	/**
	 * 年毎のエントリー数を数え上げる
	 */
	function getCountYear($labelIds){

		$labelIds = array_map(function($val) { return (int)$val; }, $labelIds);

		$binds = array(":now"=>SOYCMS_NOW);


		$spanSQL = 'SELECT max(cdate) as max, min(cdate) as min ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)';

		$result = $this->executeQuery($spanSQL,$binds);

		$max = $result[0]['max'];
		$min = $result[0]['min'];

		$maxYear = date('Y',$max);
		$minYear = date('Y',$min);

		$ret_val = array();
		$countSQL =
				'SELECT count(Entry.id) as total ' .
				'FROM Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ' .
				'WHERE EntryLabel.label_id in (' . implode(",",$labelIds) .') ' .
				'AND Entry.isPublished = 1 ' .
				'AND (Entry.openPeriodEnd > :now AND Entry.openPeriodStart <= :now)' .
				'AND (Entry.cdate >= :begin AND Entry.cdate < :end)';

		for($y = $minYear; $y <= $maxYear; $y++){
			$begin = mktime(0,0,0,1,1,$y);
			$end   = mktime(0,0,0,1,1,$y+1);

			$result = $this->executeQuery($countSQL,array(
				":begin"=>$begin,
				":end"=>$end,
				":now"=>SOYCMS_NOW
			));

			$ret_val[mktime (1, 1, 1, 1, 1, $y)] = $result[0]['total'];
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
