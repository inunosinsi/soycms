<?php
/**
 * @table soyvoice_comment
 */
class SOYVoice_Comment {

	const TYPE_MASTER = 0;  //Webマスター
	const TYPE_CUSTOMER = 1;  //お客様
	const TYPE_OTHER = 2; //その他

  	/**
	 * @id
	 */
	private $id;
	private $nickname;
	private $content;
	private $prefecture;
	private $url;
	private $email;
	private $image;
	
	/**
	 * @column user_type
	 */
	private $userType;
	
	/**
	 * @column is_published
	 */
	private $isPublished;
	
	/**
	 * @column is_entry
	 */
	private $isEntry;
	private $attribute;
	
	/**
	 * @column comment_date
	 */
	private $commentDate;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	private $reply;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getNickname(){
		return $this->nickname;
	}
	function setNIckname($nickname){
		$this->nickname = $nickname;
	}
	
	function getContent(){
		return 	$this->content;
	}
	function setContent($content){
		$this->content = $content;
	}
	
	function getPrefecture(){
		return $this->prefecture;
	}
	function setPrefecture($prefecture){
		$this->prefecture = $prefecture;
	}
	
	function getUrl(){
		return $this->url;
	}
	function setUrl($url){
		$this->url = $url;
	}
	
	function getEmail(){
		return $this->email;
	}
	function setEmail($email){
		$this->email = $email;
	}
	
	function getImage(){
		return $this->image;
	}
	function setImage($image){
		$this->image = $image;
	}
	
	function getUserType(){
		return $this->userType;
	}
	function setUserType($userType){
		$this->userType = $userType;
	}
	
	function getIsPublished(){
		return $this->isPublished;
	}
	function setIsPublished($isPublished){
		$this->isPublished = $isPublished;
	}
	
	function getIsEntry(){
		return $this->isEntry;
	}
	function setIsEntry($isEntry){
		$this->isEntry = $isEntry;
	}
	
	function getAttribute(){
		return $this->attribute;
	}
	function setAttribute($attribute){
		$this->attribute = $attribute;
	}
	
	function getCommentDate(){
		return $this->commentDate;
	}
	function setCommentDate($commentDate){
		$this->commentDate = $commentDate;
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
	
	function getReply(){
		return $this->reply;
	}
	function setReply($reply){
		$this->reply = $reply;
	}
}

?>