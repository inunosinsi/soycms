<?php
/**
 * @table NotFoundLog
 */
class NotFoundLog {

	private $ipAddressId;
	private $requestUriId;
	private $cdate;

	function getIpAddressId(){
		return $this->ipAddressId;
	}
	function setIpAddressId(int $ipAddressId){
		$this->ipAddressId = $ipAddressId;
	}

	function getRequestUriId(){
		return $this->requestUriId;
	}
	function setRequestUriId(int $requestUriId){
		$this->requestUriId = $requestUriId;
	}

	function getCdate(){
		return $this->cdate;
	}
	function setCdate($cdate){
		$this->cdate = $cdate;
	}
}
