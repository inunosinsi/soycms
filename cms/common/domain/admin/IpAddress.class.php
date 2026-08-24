<?php
/**
 * @table IpAddress
 */
class IpAddress {

	/**
	 * @id
	 */
	private $id;
	private $v4;
	/**
	 * @column is_suspicious
	 */
	private $isSuspicious=0;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getV4(){
		return $this->v4;
	}
	function setV4($v4){
		$this->v4 = $v4;
	}

	function getIsSuspicious(){
		return $this->isSuspicious;
	}

	function setIsSuspicious($isSuspicious){
		$this->isSuspicious = $isSuspicious;
	}

	/**
	 * 32ビットから192.168.0.1形式整数値に変換してから値を返す
	 */
	function getIpV4(){
		if(is_numeric($this->v4)){
			return long2ip($this->v4);
		}
		return $this->v4;
	}
	
	/**
	 * 192.168.0.1形式を32ビットの整数値に変換してからプロパティ値のv4に格納
	 */
	function setIpv4($v4){
		$this->v4 = self::convertIpv4ToInt($v4);
	}

	/**
	 * 192.168.0.1形式を32ビットの整数値に変換
	 * @param string
	 * @return int
	 */
	function convertIpv4ToInt(string $v4){
		$parts = explode('.', $v4);
		if (count($parts) !== 4) return 0;

		$res = ($parts[0] << 24) | ($parts[1] << 16) | ($parts[2] << 8) | $parts[3];
		return (int)sprintf('%u', $res);
	}
}
