<?php
/**
 * @table CmsMemo
 */
class CmsMemo {

	// メモ　アーカイブやログインしているユーザ毎にメモの表示を変えるといった対策が必要になるかもしれないから、idカラムを追加しておいた

	/**
	 * @id
	 */
	private $id;
	private $content;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}

	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
