<?php
/**
 * @table soyboard_response
 */
class SOYBoard_Response{
	
	/**
	 * @column thread_id
	 */
	private $threadId;	//書き込み対象のスレッドのID
	
	/**
	 * @id
	 */
	private $id;	//レスID
	private $name;
	private $email;
	private $submitdate;
	private $hash;	//ユーザーユニークID
	private $body;
	private $host;
	/**
	 * @column response_id
	 */
	private $responseId; //スレッドごとのレスID

	function getThreadId() {
		return $this->threadId;
	}
	function setThreadId($threadId) {
		$this->threadId = $threadId;
	}
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getEmail() {
		return $this->email;
	}
	function setEmail($email) {
		$this->email = $email;
	}
	function getSubmitdate() {
		return $this->submitdate;
	}
	function setSubmitdate($submitdate) {
		$this->submitdate = $submitdate;
	}
	function getHash() {
		return $this->hash;
	}
	function setHash($hash) {
		$this->hash = $hash;
	}

	function getBody() {
		return $this->body;
	}
	function setBody($body) {
		$this->body = $body;
	}

	function getHost() {
		return $this->host;
	}
	function setHost($host) {
		$this->host = $host;
	}
	function getResponseId() {
		return $this->responseId;
	}
	function setResponseId($ResponseId) {
		$this->responseId = $ResponseId;
	}
}
?>