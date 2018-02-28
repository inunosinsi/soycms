<?php
SOY2::import("domain.cms.Entry");
class EntryHistoryLogic extends SOY2LogicBase{

	private $dao;
	private $entryDao;

	public function __construct(){
		$this->dao = SOY2DAOFactory::create("cms.EntryHistoryDAO");
		$this->entryDao = SOY2DAOFactory::create("cms.EntryDAO");
	}

	/**
	 * IDで記事履歴を取得
	 */
	public function getHistory($id){
		$history = $this->dao->getById($id);
		return $history;
	}

	/**
	 * 記事IDで記事履歴を複数取得
	 * @param int Entry.Id
	 * @param int offset
	 * @param int limit
	 * @return array<EntryHistory>
	 */
	public function getHistories($entryId, $offset = 0, $limit = 10){
		$this->dao->setOffset($offset);
		$this->dao->setLimit($limit);
		try{
			return $this->dao->getByEntryId($entryId);
		}catch(Exception $e){
			error_log(var_export($e,true));
			return array();
		}
	}

	/**
	 * 記事の履歴数
	 * @param int Entry.Id
	 * @return int
	 */
	public function countHistories($entryId){
		try{
			return $this->dao->countByEntryId($entryId);
		}catch(Exception $e){
			return null;
		}

	}

	/**
	 * 作成時
	 * @param Entry
	 * @return int EntryHistory.id
	 */
	public function onCreate(Entry $entry){

		$bean = $this->createEntryHistory($entry);

		//新規作成
		$bean->setActionType(EntryHistory::ACTION_CREATE);

		$id = $this->dao->insert($bean);
		return $id;
	}

	/**
	 * 記事更新時
	 * @param Entry
	 * @return int EntryHistory.id
	 */
	public function onUpdate(Entry $entry){

		$bean = $this->createEntryHistory($entry);

		//更新
		$bean->setActionType(EntryHistory::ACTION_UPDATE);

		//違い
		$this->setChange($bean, $this->getLastHistory($entry->getId()));

		$id = $this->dao->insert($bean);

		return $id;
	}

	/**
	 * 記事削除時
	 * @param int Entry.id
	 * @return int EntryHistory.id
	 */
	public function onRemove($entryId){
		$bean = $this->getLastHistory($entryId);

		$id = null;

		if(!is_null($bean)){
			$bean->setActionType(EntryHistory::ACTION_REMOVE);
			$bean->setActionTarget(0);
			$bean->setChangeTitle(0);
			$bean->setChangeContent(0);
			$bean->setChangeMore(0);
			$bean->setChangeAdditional(0);
			$bean->setChangeIsPublished(0);

			try{
				$id = $this->dao->insert($bean);
			}catch(Exception $e){
				//
			}
		}

		return $id;
	}

	/**
	 * 記事複製時
	 * @param Entry 複製によって作成された記事
	 * @param int 複製元の記事のID
	 * @return int EntryHistory.id
	 */
	public function onCopy(Entry $to, $fromId){

		$bean = $this->createEntryHistory($to);

		//更新
		$bean->setActionType(EntryHistory::ACTION_COPY);
		//コピー元のIDを入れておく
		$bean->setActionTarget($fromId);

		$id = $this->dao->insert($bean);

		return $id;

	}

	/**
	 * 記事の差し戻し
	 * @param int 差し戻し対象の記事のID
	 * @param int 差し戻しに使う記事履歴のID
	 * @return int EntryHistory.id
	 * @throw Exception
	 */
	public function rollback($entryId, $historyId){
		$history = $this->getHistory($historyId);
		$entry = $this->entryDao->getById($entryId);

		$entry->setTitle($history->getTitle());
		$entry->setContent($history->getContent());
		$entry->setMore($history->getMore());

		/*
		 * 記事の公開設定は変えないので、管理者の公開権限による制限は加えない
		 */

		try{
			$this->entryDao->begin();
			$this->entryDao->update($entry);
			$this->updateAdditionals($entryId, $history->getAdditionalArray());


			$bean = $this->createEntryHistory($entry);

			//更新
			$bean->setActionType(EntryHistory::ACTION_REVERT);
			$bean->setActionTarget($historyId);

			//違い
			$this->setChange($bean, $this->getLastHistory($entryId));

			$id = $this->dao->insert($bean);

			$this->entryDao->commit();

			return $id;
		}catch(Exception $e){
			$this->entryDao->rollback();
			throw $e;
		}

	}


	/**
	 * 公開状態がpublicに変更されるときの動作
	 * @param int | array
	 * @param boolean 公開設定
	 */
	public function onPublish($id,$publish){
		if(is_array($id)){
			return $this->_onPublish($id,$publish);
		}else{
			return $this->_onPublish(array($id),$publish);
		}
	}

	/**
	 * 公開状態がpublicに変更されるときの動作
	 * @param array <Entry.id>
	 * @param boolean 公開設定
	 */
	private function _onPublish($ids,$publish){
		foreach($ids as $id){
			try{
		    	$entry = $this->entryDao->getById($id);
				$this->onUpdate($entry);
			}catch(Exception $e){
				//
			}
		}
	}

	/**
	 * クラスEntryに関連づけられていないカラムを更新する
	 * @param int $entryId
	 * @param array
	 */
	public function updateAdditionals($entryId, $additionalArray){

		//空なら何もしない
		if(!is_array($additionalArray) || count($additionalArray)<1){
			return;
		}

		//現在のテーブル構造を把握するため
		$currentAdditionalArray = $this->getAdditionalArray($entryId);
		if(!is_array($currentAdditionalArray) || count($currentAdditionalArray)<1){
			return;
		}


		$q = new SOY2DAO_Query();

		$table = $this->entryDao->getEntityInfo()->table;
		$query_a = "update ".$q->quoteIdentifier($table)." set ";
		$query_b = " where ".$q->quoteIdentifier("id")." = :id";
		foreach($additionalArray as $column => $value){
			//カラムが存在するなら更新する
			if(isset($currentAdditionalArray[$column])){
				$bindKey = ":".$column;
				$this->entryDao->executeUpdateQuery(
					$query_a.$q->quoteIdentifier($column)."=".$bindKey.$query_b,
					array(":id" => $entryId, $bindKey => $value)
				);
			}
		}

	}

	/**
	 * 記事から基本となる履歴情報を生成する
	 * @param Entry
	 * @return EntryHistory
	 */
	private function createEntryHistory(Entry $entry){
		$bean = new EntryHistory();

		$bean->setEntryId($entry->getId());

		$bean->setTitle($entry->getTitle());
		$bean->setContent($entry->getContent());
		$bean->setMore($entry->getMore());

		$bean->setAdditionalArray($this->getAdditionalArray($entry->getId()));

		$bean->setIsPublished($entry->getIsPublished());

		$bean->setActionType(0);
		$bean->setActionTarget(0);

		$bean->setChangeTitle(0);
		$bean->setChangeContent(0);
		$bean->setChangeMore(0);
		$bean->setChangeAdditional(0);
		$bean->setChangeIsPublished(0);

		return $bean;
	}

	/**
	 * カスタムフィールドなどクラスEntryのプロパティと関連付けられてないカラムの値を取得する
	 * @param int $entryId
	 * @return array
	 * @throw Exception
	 */
	 function getAdditionalArray($entryId){
		$raw = $this->entryDao->getArrayById($entryId);

		//プロパティ名 => カラム名の配列
		$columns = $this->entryDao->getEntityInfo()->getColumns();

		//Entryに含まれるデータを削除
		foreach($columns as $propName => $columnName){
			unset($raw[$columnName]);
		}

		return $raw;
	}

	/**
	 * 指定した記事の前回の履歴を取得する
	 * @param int 記事のID
	 * @return EntryHistory
	 */
	private function getLastHistory($entryId){
		try{
			return $this->dao->getLatestByEntryId($entryId);
		}catch(Exception $e){
			//error_log(var_export($e,true));
			return null;
		}
	}

	/**
	 * 変更点を取り出す
	 */
	private function setChange(EntryHistory $bean, $last){
		if($last instanceof EntryHistory){
			if($bean->getTitle() != $last->getTitle()) $bean->setChangeTitle(1);
			if($bean->getContent() != $last->getContent()) $bean->setChangeContent(1);
			if($bean->getMore() != $last->getMore()) $bean->setChangeMore(1);
			if($bean->getAdditional() != $last->getAdditional()) $bean->setChangeAdditional(1);
			if($bean->getIsPublished() != $last->getIsPublished()) $bean->setChangeIsPublished(1);
		}
	}

}
