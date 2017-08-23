<?php

/**
 * @table EntryHistory
 */
class EntryHistory {

	//新規作成
	const ACTION_CREATE = 1;
	//更新
	const ACTION_UPDATE = 2;
	//差し戻し
	const ACTION_REVERT = 3;
	//複製
	const ACTION_COPY   = 4;
	//削除
	const ACTION_REMOVE = 5;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column entry_id
	 */
	private $entryId;

	private $title;
	private $content;
	private $more;

	/**
	 * 追加のカラムの値（カスタムフィールドなど）の配列がserializeされたテキストが入る
	 * @column additional
	 */
	private $additional;

	/**
	 * 記事が公開か非公開か
	 * @column is_published
	 */
	private $isPublished;

	/**
	 * 作成日時
	 */
	private $cdate;

	/**
	 * 作業者名
	 * name
	 */
	private $author;
	/**
	 * 作業者のID
	 * @column user_id
	 */
	private $userId;

	/**
	 * 操作タイプ
	 * ACTION_...のどれかが入る
	 * @column action_type
	 */
	private $actionType;

	/**
	 * 操作関連
	 * 差し戻しなら差し戻したEntryHistory.id
	 * 複製なら複製元のEntry.id
	 * @column action_target
	 */
	private $actionTarget;

	/**
	 * 変更の有無：タイトル
	 * @column change_title
	 */
	private $changeTitle;

	/**
	 * 変更の有無：本文
	 * @column change_content
	 */
	private $changeContent;

	/**
	 * 変更の有無：追記
	 * @column change_more
	 */
	private $changeMore;

	/**
	 * 変更の有無：その他
	 * @column change_additional
	 */
	private $changeAdditional;

	/**
	 * 変更の有無：公開状態
	 * @column change_is_published
	 */
	private $changeIsPublished;


	public function getId(){
		return $this->id;
	}
	public function setId($id){
		$this->id = $id;
	}
	public function getEntryId(){
		return $this->entryId;
	}
	public function setEntryId($entryId){
		$this->entryId = $entryId;
	}
	public function getTitle(){
		return $this->title;
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function getContent(){
		return $this->content;
	}
	public function setContent($content){
		$this->content = $content;
	}
	public function getMore(){
		return $this->more;
	}
	public function setMore($more){
		$this->more = $more;
	}
	public function setAdditional($additional){
		$this->additional = $additional;
	}
	public function getCdate(){
		return $this->cdate;
	}
	public function setCdate($cdate){
		$this->cdate = $cdate;
	}
	public function getAuthor() {
		return $this->author;
	}
	public function setAuthor($author) {
		$this->author = $author;
	}
	public function getAdditional(){
		return $this->additional;
	}
	public function getUserId(){
		return $this->userId;
	}
	public function setUserId($userId){
		$this->userId = $userId;
	}
	public function getIsPublished(){
		return $this->isPublished;
	}
	public function setIsPublished($isPublished){
		$this->isPublished = (int)$isPublished;
	}
	public function getActionType(){
		return $this->actionType;
	}
	public function setActionType($actionType){
		$this->actionType = $actionType;
	}
	public function getActionTarget(){
		return $this->actionTarget;
	}
	public function setActionTarget($actionTarget){
		$this->actionTarget = $actionTarget;
	}
	public function getChangeTitle(){
		return $this->changeTitle;
	}
	public function setChangeTitle($changeTitle){
		$this->changeTitle = $changeTitle;
	}
	public function getChangeContent(){
		return $this->changeContent;
	}
	public function setChangeContent($changeContent){
		$this->changeContent = $changeContent;
	}
	public function getChangeMore(){
		return $this->changeMore;
	}
	public function setChangeMore($changeMore){
		$this->changeMore = $changeMore;
	}
	public function getChangeAdditional(){
		return $this->changeAdditional;
	}
	public function setChangeAdditional($changeAdditional){
		$this->changeAdditional = $changeAdditional;
	}
	public function getChangeIsPublished(){
		return $this->changeIsPublished;
	}
	public function setChangeIsPublished($changeIsPublished){
		$this->changeIsPublished = $changeIsPublished;
	}

	/**
	 * 配列で操作
	 */
	public function getAdditionalArray(){
		return unserialize($this->additional);
	}
	public function setAdditionalArray(Array $additional){
		$this->additional = serialize($additional);
	}

	/**
	 * テキストで取得
	 */
	public function getActionTypeText(){
		switch($this->actionType){
			case self::ACTION_CREATE:
				return CMSMessageManager::get("SOYCMS_CREATE");
			case self::ACTION_UPDATE:
				return CMSMessageManager::get("SOYCMS_UPDATE");
			case self::ACTION_COPY:
				return CMSMessageManager::get("SOYCMS_COPY") . " (".$this->actionTarget.")";
			case self::ACTION_REVERT:
				return CMSMessageManager::get("SOYCMS_RECOVER") . " (".$this->actionTarget.")";
			case self::ACTION_REMOVE:
				return CMSMessageManager::get("SOYCMS_DELETE");
			default:
				return CMSMessageManager::get("SOYCMS_UNKNOWN");

		}
	}
	public function getChangeText(){
		$text = array();
		if($this->changeTitle) $text[] = CMSMessageManager::get("SOYCMS_ENTRY_TITLE");
		if($this->changeContent) $text[] = CMSMessageManager::get("SOYCMS_ENTRY_CONTENT");
		if($this->changeMore) $text[] = CMSMessageManager::get("SOYCMS_ENTRY_MORE");
		if($this->changeAdditional) $text[] = CMSMessageManager::get("SOYCMS_ENTRY_ETC");
		return implode(", ",$text);
	}
	public function getPublishStatusText(){
		if($this->isPublished){
			return CMSMessageManager::get("SOYCMS_PUBLISHED");
		}else{
			return CMSMessageManager::get("SOYCMS_NOT_PUBLISHED");
		}
	}

}

