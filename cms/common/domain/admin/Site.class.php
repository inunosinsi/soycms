<?php
/**
 * @table Site
 * @date 2007-08-22 18:42:19
 */
class Site {

	const TYPE_SOY_CMS = 1;
	const TYPE_SOY_SHOP = 2;


	/**
	 * @id identity
	 */
	private $id;

	/**
	 * @column site_id
	 */
	private $siteId;

	/**
	 * @column site_name
	 */
	private $siteName;

	/**
	 * @column site_type
	 */
	private $siteType = self::TYPE_SOY_CMS;


	private $url;

	private $path;

	/**
	 * @column data_source_name
	 */
	private $dataSourceName;

	private $isDomainRoot = false;

	public function setId($id){
		$this->id = $id;
	}

	public function getId(){
		return $this->id;
	}

	public function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	public function getSiteId(){
		return $this->siteId;
	}

	function getSiteName() {
		return $this->siteName;
	}
	function setSiteName($siteName) {
		$this->siteName = $siteName;
	}
	public function getSiteType() {
		return $this->siteType;
	}
	public function setSiteType($siteType) {
		$this->siteType = $siteType;
	}
	function getIsDomainRoot() {
		return (int)$this->isDomainRoot;
	}
	function setIsDomainRoot($isDomainRoot) {
		$this->isDomainRoot = $isDomainRoot;
	}

	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getPath() {
		return $this->path;
	}
	function setPath($path) {
		$this->path = $path;
	}

	function getDataSourceName() {
		return $this->dataSourceName;
	}
	function setDataSourceName($dataSourceName) {
		$this->dataSourceName = $dataSourceName;
	}

	/* Site */
	static public function getSiteTypes(){
		return array(
			"SOY CMS" => self::TYPE_SOY_CMS,
			"SOY Shop" => self::TYPE_SOY_SHOP
		);
	}

	/* util */
	function getLoginLink($param = array()){

		$link = SOY2PageController::createLink("Site.Login.".$this->getId());

		switch($this->getSiteType()){
			case self::TYPE_SOY_SHOP:
				$param = array();//リセット
				$param["site_id"] = $this->getSiteId();
				$link = SOY2PageController::createLink("Site.Login.0").( count($param) ? "?".http_build_query($param) : "" );
				break;
			case self::TYPE_SOY_CMS:
			default:
				$link = SOY2PageController::createLink("Site.Login.".$this->getId()).( count($param) ? "?".http_build_query($param) : "" );
		}

		return $link;
	}

	function getIsMySQL(){
		$dsn = $this->getDataSourceName();
		$str = substr($dsn,0,6);
		$res = strpos($str,"mysql");

		return is_int($res);
	}
}
?>