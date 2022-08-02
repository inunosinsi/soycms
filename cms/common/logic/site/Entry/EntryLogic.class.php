<?php

class EntryLogic extends SOY2LogicBase{

	private $offset;
	private $limit;
	private $reverse = false;//逆順にする（DisplayOrder以外のcdate,idの部分のみ）
	private $blockClass;	//ブロックのクラス
	private $totalCount;

	function __construct(){
		/** @ToDo LabeledEntryで最新版のSQLiteに対応したい **/
		SOY2::import("logic.site.Entry.class.new.LabeledEntryDAO");
	}

	function getLimit(){
		return (is_numeric($this->limit)) ? (int)$this->limit : -1;
	}
	function setLimit($limit){
		$this->limit = $limit;
	}

	function getOffset(){
		return (is_numeric($this->offset)) ? (int)$this->offset : -1;
	}
	function setOffset($offset){
		$this->offset  =$offset;
	}

	function setReverse($reverse){
		$this->reverse  =$reverse;
	}

	function setBlockClass($blockClass){
		$this->blockClass = $blockClass;
	}

	 /**
 	 * エントリーを新規作成
 	 */
 	function create(Entry $bean){

 		$dao = self::entryDao();

 		if(is_string($bean->getContent())) $bean->setContent(self::_cleanupMCETags($bean->getContent()));
 		if(is_string($bean->getMore())) $bean->setMore(self::_cleanupMCETags($bean->getMore()));

 		//数値以外（空文字列を含む）がcdateに入っていれば現在時刻を作成日時にする
 		if(!is_numeric($bean->getCdate())){
 			$bean->setCdate(SOYCMS_NOW);
 		}

		SOY2::import("util.UserInfoUtil");
 		if(UserInfoUtil::hasEntryPublisherRole() != true){
 			$bean->setOpenPeriodEnd(CMSUtil::encodeDate(null,false));
 			$bean->setOpenPeriodStart(CMSUtil::encodeDate(null,true));

 			$bean->setIsPublished(false);
 		}

 		//仮で今の時間を入れておく カスタムエイリアス　SQLite対策
 		if($bean->getId() == $bean->getAlias()) $bean->setAlias((string)time());
		$id = $dao->insert($bean);
		
		$newAlias = $this->getUniqueAlias($id, (string)$bean->getTitle());		
		if($bean->getAlias() != $newAlias){
			$bean->setId($id);//updateを実行するため
			$bean->setAlias($newAlias);
			$dao->update($bean);
		}

 		return $id;
 	}

	/**
	 * エントリーを更新
	 */
	function update(Entry $bean){

		$dao = self::entryDao();

		//数値以外（空文字列を含む）がcdateに入っていれば現在時刻を作成日時にする
		if(!is_numeric($bean->getCdate())){
			$bean->setCdate(SOYCMS_NOW);
		}
		
		if($bean->isEmptyAlias()){
			$bean->setAlias($this->getUniqueAlias($bean->getId(),$bean->getTitle()));
		}

		if(is_string($bean->getContent())) $bean->setContent(self::_cleanupMCETags($bean->getContent()));
		if(is_string($bean->getMore())) $bean->setMore(self::_cleanupMCETags($bean->getMore()));

		if(UserInfoUtil::hasEntryPublisherRole() != true){
			$old = $dao->getById($bean->getId());
			$bean->setOpenPeriodEnd(CMSUtil::encodeDate($old->getOpenPeriodEnd(),false));
			$bean->setOpenPeriodStart(CMSUtil::encodeDate($old->getOpenPeriodStart(),true));

			$bean->setIsPublished($old->getIsPublished());

		}else{
			$bean->setOpenPeriodEnd(CMSUtil::encodeDate($bean->getOpenPeriodEnd(),false));
			$bean->setOpenPeriodStart(CMSUtil::encodeDate($bean->getOpenPeriodStart(),true));
		}
		
		try{
			$dao->update($bean);
		}catch(Exception $e){
			//
		}
		
		return $bean->getId();
	}

	function deleteByIds(array $ids){
		$dao = self::entryDao();
		$entryLabelDao = self::entryLabelDao();
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
	function getById($id, bool $flag=true) {
		$dao = self::entryDao();
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
		$dao = self::entryDao();

		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());
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
	function getByLabelId(int $labelId){
		return $this->getByLabelIds(array($labelId));
	}

	/**
	 * 非公開のエントリーを取得
	 */
	function getClosedEntryList(){
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());

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
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());

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
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());

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
	function getByLabelIds(array $labelIds, bool $flag=true, int $start=Entry::PERIOD_START, int $end=Entry::PERIOD_END){
		$dao = self::labeledEntryDao();

		$array = $dao->getByLabelIdsOnlyId($labelIds, $this->reverse, $this->getLimit(), $this->getOffset());
		$this->totalCount = $dao->countByLabelIdsOnlyId($labelIds);

		//ラベルを取得
		foreach($array as $key => $entry){
			$array[$key] = $this->getById($key, false);
			$array[$key]->setLabels($this->getLabelIdsByEntryId($entry->getId()));
		}

		return $array;
	}

	/**
	 * エントリーに割り当てているラベルIDを全て取得
	 */
	function getLabelIdsByEntryId(int $entryId){
		$dao = self::entryLabelDao();

		$entryLabels = $dao->getByEntryId($entryId);
		$result = array();
		foreach($entryLabels as $obj){
			$result[] = $obj->getLabelId();
		}

		return $result;
	}

	function getLabeledEntryByEntryId(int $entryId){
		return self::entryLabelDao()->getByEntryId($entryId);
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
	function setEntryLabel(int $entryId, int $labelId){
		self::entryLabelDao()->setByParams($entryId,$labelId);
	}

	/**
	 * エントリーについているラベルを全て削除
	 */
	function clearEntryLabel(int $entryId){
		self::entryLabelDao()->deleteByEntryId($entryId);
	}

	/**
	 * エントリーからラベルを削除
	 */
	function unsetEntryLabel(int $entryId, int $labelId){
		self::entryLabelDao()->deleteByParams($entryId,$labelId);
	}


	/**
	 * 表示順の更新
	 */
	function updateDisplayOrder(int $entryId, int $labelId, int $displayOrder){
		$dao = self::entryLabelDao();
		$dao->deleteByParams($entryId,$labelId);
		$dao->setByParams($entryId,$labelId,$displayOrder);
	}

	/**
	 * ラベルとエントリーに対応する表示順を返す
	 */
	function getDisplayOrder(int $entryId, int $labelId){
		try{
			return self::entryLabelDao()->getByEntryIdLabelId($entryId,$labelId)->getDisplayOrder();
		}catch(Exception $e){
			return null;
		}
	}

	/**
	 * 表示期間を含めたラベル付けされたエントリーを取得
	 */
	function getOpenEntryByLabelId(int $labelId){
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());
		//仕様変更により、記事取得関数実行時に念の為にlimitとoffsetを渡しておく
		$arr = $dao->getOpenEntryByLabelId((int)$labelId, SOYCMS_NOW, $this->reverse, $this->getLimit(), $this->getOffset());
		$this->totalCount = $dao->getRowCount();
		return $arr;
	}

	/**
	 * 表示期間を含めてラベル付けされたエントリーを取得（ラベルIDを複数指定）
	 */
	function getOpenEntryByLabelIds(array $labelIds, bool $isAnd=true, int $start=Entry::PERIOD_START, int $end=Entry::PERIOD_END){
		$dao = self::labeledEntryDao();
		$dao->setBlockClass($this->blockClass);

		if($isAnd){
			//$labelIdsのラベルがすべて設定されている記事のみ取得
			$array = $dao->getOpenEntryByLabelIds($labelIds,SOYCMS_NOW,$start,$end,$this->reverse, $this->getLimit(), $this->getOffset());
			$this->totalCount = $dao->countOpenEntryByLabelIds($labelIds, SOYCMS_NOW, $isAnd, $start, $end);
		}else{
			//$labelIdsのラベルがどれか１つでも設定されている記事を取得
			$array = $dao->getOpenEntryByLabelIdsImplements($labelIds,SOYCMS_NOW,false,$start,$end,$this->reverse, $this->getLimit(), $this->getOffset());
			$this->totalCount = $dao->countOpenEntryByLabelIds($labelIds, SOYCMS_NOW, $isAnd, $start, $end);
		}
		foreach($array as $key => $entry){
			$array[$key]->setCommentCount($this->getApprovedCommentCountByEntryId($entry->getId()));
			$array[$key]->setTrackbackCount($this->getCertificatedTrackbackCountByEntryId($entry->getId()));
		}

		return $array;
	}

	/**
	 * ブログのエントリーを取得
	 * @param int int|string
	 * @return LabeledEntry
	 */
	function getBlogEntry(int $blogLabelId, $entryId){
		$dao = self::entryDao();

		if(defined("CMS_PREVIEW_ALL")){
			if(is_numeric($entryId)){
				try{
					$entry = $dao->getById($entryId);
				}catch(Exception $e){
					try{
						$entry = $dao->getByAlias($entryId);
					}catch(Exception $e){
						$entry = new Entry();
					}
				}
			}else{
				try{
					$entry = $dao->getByAlias($entryId);
				}catch(Exception $e){
					$entry = new Entry();
				}
			}
		}else{
			if(is_numeric($entryId)){
				try{
					$entry = $dao->getOpenEntryById($entryId, SOYCMS_NOW);
				}catch(Exception $e){
					//記事IDで取得できなければ、エイリアスの方でも取得を試みる
					try{
						$entry = $dao->getOpenEntryByAlias($entryId, SOYCMS_NOW);
					}catch(Exception $e){
						$entry = new Entry();
					}
				}
			}else{
				try{
					$entry = $dao->getOpenEntryByAlias($entryId, SOYCMS_NOW);
				}catch(Exception $e){
					$entry = new Entry();
				}
			}
		}

		$entry = SOY2::cast("LabeledEntry", $entry);
		if(!is_numeric($entry->getId())) return $entry;
		
		//ブログに所属しているエントリーかどうかチェックする
		$labelIds = $this->getLabelIdsByEntryId($entry->getId());
		return (in_array($blogLabelId, $labelIds)) ? $entry : new LabeledEntry();	//throw new Exception("This entry (id: {$entryId}) does not belong to the designated blog (label: {$blogLabelId}).");
	}

	/**
	 * @param int, int|string
	 * @return Entry
	 */
	function getBlogEntryWithoutExecption(int $blogLabelId, $entryId){
		$dao = self::entryDao();
		if(is_numeric($entryId)){
			try{
				return $dao->getOpenEntryById($entryId,SOYCMS_NOW);
			}catch(Exception $e){
				//記事IDで取得できなければ、エイリアスの方でも取得を試みる
				try{
					return $dao->getOpenEntryByAlias($entryId,SOYCMS_NOW);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				return $dao->getOpenEntryByAlias($entryId,SOYCMS_NOW);
			}catch(Exception $e){
				//
			}
		}
		return new Entry();
	}

	/**
	 * 次のエントリーを取得
	 */
	function getNextOpenEntry(int $blogLabelId, LabeledEntry $entry){
		$dao = self::labeledEntryDao();
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
	function getPrevOpenEntry(int $blogLabelId, LabeledEntry $entry){
		$dao = self::labeledEntryDao();
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
	 * @param int|array, int
	 * @return bool
	 */
	function setPublish($id, int $publish){
		$dao = self::entryDao();
		if(is_array($id)){
			//配列だったらそれぞれを設定
			try{
				$dao->begin();
				foreach($id as $pId){
					$dao->setPublish($pId, $publish);
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
				$dao->setPublish($id, $publish);
				return true;
			}catch(Exception $e){
				return false;
			}
		}
	}

	/**
	 * 月別アーカイブを数える
	 */
	function getCountMonth(array $labelIds){
		return self::labeledEntryDao()->getCountMonth($labelIds);
	}

	function getMonth(array $labelIds){
		return self::labeledEntryDao()->getMonth($labelIds);
	}

	/**
	 * 年別アーカイブを数える
	 */
	function getCountYear(array $labelIds){
		return self::labeledEntryDao()->getCountYear($labelIds);
	}

	/**
	 * ラベルIDを複数指定し、公開しているエントリー数を数え上げる
	 */
	function getOpenEntryCountByLabelIds(array $labelIds){
		$dao = self::labeledEntryDao();
		try{
			$dao->getOpenEntryCountByLabelIds($labelIds, SOYCMS_NOW);
			return $dao->getRowCount();
		}catch(Exception $e){
			return 0;
		}
	}

	/**
	 * ラベルID（複数）からエントリーを取得
	 */
	function getEntryByLabelIds(array $labelIds){
		$dao = self::entryDao();

		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());
		try{
			$array = $dao->getEntryByLabelIds($labelIds);
			$this->totalCount = $dao->getRowCount();
		}catch(Exception $e){
			$this->totalCount = 0;
			$array = array();
		}
		if(!count($array)) return array();
		
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
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->getLimit());
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
	function getRecentEntriesByLabelId(int $labelId){
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->getLimit());
		return $dao->getRecentEntriesByLabelId($labelId);
	}

	/**
	 * 最近使用されたエントリーを取得（管理側で使用）
	 */
	function getRecentEntries(){
		$dao = self::entryDao();
		$dao->setLimit($this->getLimit());
		$dao->setOffset($this->getOffset());
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
	private function _cleanupMCETags(string $html){
		return  preg_replace('/<p><\/p>|<p\s+\/>/','<br />',preg_replace('/\s?mce_[a-zA-Z0-9_]+\s*=\s*"[^"]*"/','',$html));
	}

	/**
	 * コメント数を取得
	 */
	function getCommentCount(int $entryId){
		return ($entryId > 0) ? SOY2DAOFactory::create("cms.EntryCommentDAO")->getCommentCountByEntryId($entryId) : 0;
	}

	function getApprovedCommentCountByEntryId(int $entryId){
		return ($entryId > 0) ? SOY2DAOFactory::create("cms.EntryCommentDAO")->getApprovedCommentCountByEntryId($entryId) : 0;
	}

	/**
	 * トラックバック数を取得
	 */
	function getTrackbackCount(int $entryId){
		return ($entryId > 0) ? SOY2DAOFactory::create("cms.EntryTrackbackDAO")->getTrackbackCountByEntryId($entryId) : 0;
	}

	function getCertificatedTrackbackCountByEntryId(int $entryId){
		return ($entryId > 0) ? SOY2DAOFactory::create("cms.EntryTrackbackDAO")->getCertificatedTrackbackCountByEntryId($entryId) : 0;
	}

	/**
	 * getUniqueAlias
	 * ユニークなエイリアスを取得
	 */
	function getUniqueAlias(int $id, string $title){
		$dao = self::entryDao();

		//[?#\/%\&]は取り除く
		//2009-02-17 CGIモードで不具合が出るので & も削除
		//2009-02-17 Labelでも使うのでCMSUtil::sanitizeAliasに移動
		$title = CMSUtil::sanitizeAlias($title);

		//数字だけの場合は_を前につける
		if(is_numeric($title)){
			$title = "_".$title;
		}

		try{
			if($dao->getByAlias($title)->getId() == $id) return $title;
		}catch(Exception $e){
			//none
			return $title;
		}

		return $title."_".$id;
	}

	private function entryDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryDAO");
		return $dao;
	}
	private function entryLabelDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		return $dao;
	}
	private function labeledEntryDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("LabeledEntryDAO");
		return $dao;
	}
}
