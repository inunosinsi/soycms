<?php
/**
 * @table RequestUri
 */
class RequestUri {

	/**
	 * @id
	 */
	private $id;
	private $uri;
	private $hash;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getUri(){
		return $this->uri;
	}
	function setUri($uri){
		$this->uri = $uri;
	}

	function getHash(){
		return $this->hash;
	}
	function setHash($hash){
		$this->hash = $hash;
	}

	function hashAndSet($str){
		$this->hash = self::createHash($str);
	}

	function createHash(string $str){
		return md5($str, true);
	}
}
