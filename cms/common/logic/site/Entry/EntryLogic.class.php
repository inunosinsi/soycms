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

	function setLimit($limit){
		$this->limit = $limit;
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
	 /**
 	 * エントリーを新規作成
 	 */
 	function create(Entry $bean){

 		$dao = self::entryDao();

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

 		//仮で今の時間を入れておく カスタムエイリアス　SQLite対策
 		if($bean->getId() == $bean->getAlias()) $bean->setAlias(time());
 		$id = $dao->insert($bean);

 		//if($bean->isEmptyAlias()){	//ここのif文は必要ない
 			$bean->setId($id);//updateを実行するため
 			$bean->setAlias($this->getUniqueAlias($id,$bean->getTitle()));
 			$dao->update($bean);
 		//}

 		return $id;
 	}

	/**
	 * エントリーを更新
	 */
	function update($bean){

		$dao = self::entryDao();

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
	function getById($id,$flag = true) {
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
		$dao = self::labeledEntryDao();
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
		$dao = self::labeledEntryDao();
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
		$dao = self::labeledEntryDao();
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
	function getByLabelIds($labelIds,$flag = true, $start = null, $end = null){
		$dao = self::labeledEntryDao();

		$array = $dao->getByLabelIdsOnlyId($labelIds, $this->reverse, $this->limit, $this->offset);
		$this->totalCount = $dao->countByLabelIdsOnlyId($labelIds);

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
		$dao = self::entryLabelDao();

		$entryLabels = $dao->getByEntryId($entryId);
		$result = array();
		foreach($entryLabels as $obj){
			$result[] = $obj->getLabelId();
		}

		return $result;
	}

	function getLabeledEntryByEntryId($entryId){
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
	function setEntryLabel($entryId,$labelId){
		self::entryLabelDao()->setByParams($entryId,$labelId);
	}

	/**
	 * エントリーについているラベルを全て削除
	 */
	function clearEntryLabel($entryId){
		self::entryLabelDao()->deleteByEntryId($entryId);
	}

	/**
	 * エントリーからラベルを削除
	 */
	function unsetEntryLabel($entryId,$labelId){
		self::entryLabelDao()->deleteByParams($entryId,$labelId);
	}


	/**
	 * 表示順の更新
	 */
	function updateDisplayOrder($entryId,$labelId,$displayOrder){
		$dao = self::entryLabelDao();
		$dao->deleteByParams($entryId,$labelId);
		$dao->setByParams($entryId,$labelId,$displayOrder);
	}

	/**
	 * ラベルとエントリーに対応する表示順を返す
	 */
	function getDisplayOrder($entryId,$labelId){
		try{
			return self::entryLabelDao()->getByEntryIdLabelId($entryId,$labelId)->getDisplayOrder();
		}catch(Exception $e){
			return null;
		}
	}

	/**
	 * 表示期間を含めたラベル付けされたエントリーを取得
	 */
	function getOpenEntryByLabelId($labelId){
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->limit);
		$dao->setOffset($this->offset);
		//仕様変更により、記事取得関数実行時に念の為にlimitとoffsetを渡しておく
		$array = $dao->getOpenEntryByLabelId($labelId,SOYCMS_NOW,$this->reverse, $this->limit, $this->offset);
		$this->totalCount = $dao->getRowCount();
		return $array;
	}

	/**
	 * 表示期間を含めてラベル付けされたエントリーを取得（ラベルIDを複数指定）
	 */
	function getOpenEntryByLabelIds($labelIds,$isAnd = true, $start = null, $end = null){
		$dao = self::labeledEntryDao();
		$dao->setBlockClass($this->blockClass);

		if($isAnd){
			//$labelIdsのラベルがすべて設定されている記事のみ取得
			$array = $dao->getOpenEntryByLabelIds($labelIds,SOYCMS_NOW,$start,$end,$this->reverse, $this->limit, $this->offset);
			$this->totalCount = $dao->countOpenEntryByLabelIds($labelIds, SOYCMS_NOW, $isAnd, $start, $end);
		}else{
			//$labelIdsのラベルがどれか１つでも設定されている記事を取得
			$array = $dao->getOpenEntryByLabelIdsImplements($labelIds,SOYCMS_NOW,false,$start,$end,$this->reverse, $this->limit, $this->offset);
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
	 */
	function getBlogEntry($blogLabelId,$entryId){
		$dao = self::entryDao();

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

	function getBlogEntryWithoutExecption($blogLabelId,$entryId){
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
	function getNextOpenEntry($blogLabelId,$entry){
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
	function getPrevOpenEntry($blogLabelId,$entry){
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
	 */
	function setPublish($id,$publish){
		$dao = self::entryDao();
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
		return self::labeledEntryDao()->getCountMonth($labelIds);
	}

	function getMonth($labelIds){
		return self::labeledEntryDao()->getMonth($labelIds);
	}

	/**
	 * 年別アーカイブを数える
	 */
	function getCountYear($labelIds){
		return self::labeledEntryDao()->getCountYear($labelIds);
	}

	/**
	 * ラベルIDを複数指定し、公開しているエントリー数を数え上げる
	 */
	function getOpenEntryCountByLabelIds($labelIds){
		$dao = self::labeledEntryDao();
		$dao->getOpenEntryCountByLabelIds($labelIds,SOYCMS_NOW);
		$count = $dao->getRowCount();
		return $count;
	}

	/**
	 * ラベルID（複数）からエントリーを取得
	 */
	function getEntryByLabelIds($labelIds){
		$dao = self::entryDao();

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
		$dao = self::labeledEntryDao();
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
		$dao = self::labeledEntryDao();
		$dao->setLimit($this->limit);
		return $dao->getRecentEntriesByLabelId($labelId);
	}

	/**
	 * 最近使用されたエントリーを取得（管理側で使用）
	 */
	function getRecentEntries(){
		$dao = self::entryDao();
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
		return SOY2DAOFactory::create("cms.EntryCommentDAO")->getCommentCountByEntryId($entryId);
	}

	function getApprovedCommentCountByEntryId($entryId){
		return SOY2DAOFactory::create("cms.EntryCommentDAO")->getApprovedCommentCountByEntryId($entryId);
	}

	/**
	 * トラックバック数を取得
	 */
	function getTrackbackCount($entryId){
		return SOY2DAOFactory::create("cms.EntryTrackbackDAO")->getTrackbackCountByEntryId($entryId);
	}

	function getCertificatedTrackbackCountByEntryId($entryId){
		return SOY2DAOFactory::create("cms.EntryTrackbackDAO")->getCertificatedTrackbackCountByEntryId($entryId);
	}

	/**
	 * getUniqueAlias
	 * ユニークなエイリアスを取得
	 */
	function getUniqueAlias($id,$title){
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
