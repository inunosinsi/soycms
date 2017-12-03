<?php

/**
 * @table Entry inner join EntryComment on (EntryComment.entry_id = Entry.id) inner join EntryLabel on (EntryLabel.entry_id = Entry.id)
 */
class EntryCommentLogic implements SOY2LogicInterface{

	public static function getInstance($className,$args){
		return SOY2LogicBase::getInstance($className,$args);
	}

	/**
	 * @column EntryComment.id
	 * @alias id
	 */
	private $id;

	/**
	 * @column EntryComment.title
	 * @alias title
	 */
	private $title;

	/**
	 * @column EntryComment.author
	 * @alias author
	 */
	private $author;

	/**
	 * @column EntryComment.body
	 * @alias body
	 */
	private $body;

	/**
	 * @column Entry.alias
	 * @alias alias
	 */
	private $alias;

	/**
	 * @column Entry.title as entryTitle
	 * @alias entryTitle
	 */
	private $entryTitle;

	/**
	 * @column EntryLabel.label_id
	 * @alias label_id
	 */
	private $labelId;

	/**
	 * @column EntryComment.submitdate
	 * @alias submitdate
	 */
	private $submitDate;

	/**
	 * @column EntryComment.entry_id
	 * @alias entry_id
	 */
	private $entryId;

	/**
	 * @column EntryComment.is_approved as isApproved
	 * @alias isApproved
	 */
	private $isApproved;

	/**
	 * @no_persistent
	 */
	private $totalCount;

	/**
	 * @column EntryComment.mail_address as mailAddress
	 * @alias mailAddress
	 */
	private $mailAddress;

	/**
	 * @column EntryComment.url
	 * @alias url
	 */
	private $url;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getAuthor() {
		return $this->author;
	}
	function setAuthor($author) {
		$this->author = $author;
	}
	function getAlias() {
		return $this->alias;
	}
	function setAlias($alias) {
		$this->alias = $alias;
	}
	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}

	function getRecentComments($labelIds,$count = 10){
		$dao = SOY2DAOFactory::create("EntryCommentLogicDAO");
		$dao->setLimit($count);
		$comments = $dao->getOpenCommentByLabelIds($labelIds,time());
		return $comments;
	}

	function getComments($labelIds,$count,$offset){
		$dao = SOY2DAOFactory::create("EntryCommentLogicDAO");
		$dao->setLimit($count);
		$dao->setOffset($offset);
		$retVal =$dao->getByLabelIds($labelIds);
		$this->setTotalCount($dao->getRowCount());
		return $retVal;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}

	function getSubmitDate() {
   		return $this->submitDate;
   	}
   	function setSubmitDate($submitDate) {
   		$this->submitDate = $submitDate;
   	}
   	function getEntryId() {
   		return $this->entryId;
   	}
   	function setEntryId($entryId) {
   		$this->entryId = $entryId;
   	}

   	function getEntryTitle() {
   		return $this->entryTitle;
   	}
   	function setEntryTitle($entryTitle) {
   		$this->entryTitle = $entryTitle;
   	}
   	function getBody() {
   		return $this->body;
   	}
   	function setBody($body) {
   		$this->body = $body;
   	}
   	function getIsApproved() {
   		return $this->isApproved;
   	}
   	function setIsApproved($isApproved) {
   		$this->isApproved = $isApproved;
   	}


   	function getTotalCount() {
   		return $this->totalCount;
   	}
   	function setTotalCount($totalCount) {
   		$this->totalCount = $totalCount;
   	}

   	function getUrl() {
   		return $this->url;
   	}
   	function setUrl($url) {
   		$this->url = $url;
   	}

   	function getMailAddress() {
   		return $this->mailAddress;
   	}
   	function setMailAddress($mailAddress) {
   		$this->mailAddress = $mailAddress;
   	}

   	function delete($commentId){
   		$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
    	return $dao->delete($commentId);
   	}

   	function toggleApproved($commentId,$state){
   		$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
    	return $dao->setApproved($commentId,$state);
   	}
}

/**
 * @entity EntryCommentLogic
 */
abstract class EntryCommentLogicDAO extends SOY2DAO{
	abstract function get();
	abstract function getByLabelId($labelId);

	/**
	 * @order #submitDate# DESC
	 */
	function getOpenCommentByLabelIds($labelIds,$time){
		$query = $this->getQuery();
		$labelIds = array_map(function($val) { return (int)$val; } ,$labelIds);
		if(count($labelIds)>0){
			$query->where = " EntryLabel.label_id in (" . implode(",",$labelIds) .") AND ";
		}
		$query->where .= " Entry.isPublished = 1 ";
		$query->where .= "AND (openPeriodEnd > :now AND openPeriodStart < :now)";
		$query->where .= "AND is_approved = 1";

		$binds = array(
			":now" => $time
		);

		$result = $this->executeQuery($query,$binds);

		$array = array();
		foreach($result as $row){
			$array[] = $this->getObject($row);
		}

		return $array;
	}

	/**
	 * @order #submitDate# DESC
	 */
	function getByLabelIds($labelIds){
		$query = $this->getQuery();

		$labelIds = array_map(function($val) { return (int)$val; }, $labelIds);

		if(count($labelIds)>0){
			$query->where = " EntryLabel.label_id in (" . implode(",",$labelIds) .") ";
		}

		$result = $this->executeQuery($query,array());
		$array = array();
		foreach($result as $row){
			$array[] = $this->getObject($row);
		}

		return $array;
	}
}

?>
