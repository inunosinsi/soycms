<?php
/**
 * @table soylpo_list
 */
class SOYLpo_List {
	
	const MODE_DEFAULT = 0;
	const MODE_REFERER = 1;	//検索サイトから
	const MODE_DOMAIN = 2;	//特定のサイトから
	const MODE_URL = 3;		//特定のURLから

	/**
	 * @id
	 */
	private $id;
	private $title;
	private $content;
	private $mode;
	private $keyword;
	
	/**
	 * @column is_public
	 */
	private $isPublic;
	
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
	
	function getTitle(){
		return $this->title;
	}
	function setTitle($title){
		$this->title = $title;
	}
	
	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}
	
	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
	
	public function getModeText(){
		$mode = self::getMode();
		$list = self::getModeList();
		
		return $list[$mode];
	}
	
	public function getModeList(){
		return array(
					self::MODE_DEFAULT => "ディフォルト",
					self::MODE_REFERER => "検索サイト",
					self::MODE_DOMAIN => "指定のサイト",
					self::MODE_URL => "指定のページ"
					);
	}
	
	function getKeyword(){
		return $this->keyword;
	}
	function setKeyword($keyword){
		$this->keyword = $keyword;
	}
	
	function getIsPublic(){
		return $this->isPublic;
	}
	function setIsPublic($isPublic){
		$this->isPublic = $isPublic;
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
?>