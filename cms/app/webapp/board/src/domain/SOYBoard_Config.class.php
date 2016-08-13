<?php
/**
 * @table soyboard_config
 */
class SOYBoard_Config{
	
	/**
	 * @column thread_id
	 */
	private $threadId;

	
	/**
	 * @column default_name
	 */
	private $defaultName;
	
	/**
	 * @column is_stopped
	 */
	private $isStopped;
	
	/**
	 * @column max_response
	 */
	private $maxResponse;

	/**
	 * @column show_host
	 */
	private $showHost;	
	
	/**
	 * @column sage_accept
	 */
	private $sageAccept;

	function getThreadId() {
		return $this->threadId;
	}
	function setThreadId($threadId) {
		$this->threadId = $threadId;
	}
	
	function getDefaultName() {
		return $this->defaultName;
	}
	function setDefaultName($defaultName) {
		$this->defaultName = $defaultName;
	}
	function getIsStopped() {
		return $this->isStopped;
	}
	function setIsStopped($isStopped) {
		$this->isStopped = $isStopped;
	}

	function getMaxResponse() {
		return $this->maxResponse;
	}
	function setMaxResponse($maxResponse) {
		$this->maxResponse = $maxResponse;
	}
	function getShowHost() {
		return $this->showHost;
	}
	
	function setShowHost($showHost) {
		$this->showHost = $showHost;
	}

	function getSageAccept() {
		return $this->sageAccept;
	}
	
	function setSageAccept($sageAccept) {
		$this->sageAccept = $sageAccept;
	}

}
?>