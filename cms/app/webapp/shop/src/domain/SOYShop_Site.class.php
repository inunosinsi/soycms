<?php
/**
 * 新規追加用のダミークラス
 * SOYShop_SiteとSOYShop_Siteを置換すればOK
 * @table soyshop_site
 */
class SOYShop_Site extends SOY2DAO_EntityBase{

	const SUPER_USER = 1;  //受注管理とテンプレート編集ができる
	const ORDER_USER = 2;  //受注管理とCSVのインポート等までできる
	const ORDER_LIMIT = 3;	//受注管理のみ


    /**
	 * @id
	 */
	private $id;

	/**
	 * @column site_id
	 */
	private $siteId;

	/**
	 * @column site_name
	 */
	private $name;

	private $url;

	private $path;

	/**
	 * @column data_source_name
	 */
	private $dsn;


	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function check(){
		return true;
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}

	public function getSiteId() {
		return $this->siteId;
	}
	public function setSiteId($siteId) {
		$this->siteId = $siteId;
	}

	public function getName() {
		return $this->name;
	}
	public function setName($name) {
		$this->name = $name;
	}

	public function getUrl() {
		return $this->url;
	}
	public function setUrl($url) {
		$this->url = $url;
	}

	public function getPath() {
		return $this->path;
	}
	public function setPath($path) {
		$this->path = $path;
	}

	public function getDsn() {
		return $this->dsn;
	}
	public function setDsn($dsn) {
		$this->dsn = $dsn;
	}

	function getCreateDate() {
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}

	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}

	/* util */
	function getIsMySQL(){
		$dsn = $this->getDsn();
		$str = substr($dsn,0,6);
		$res = strpos($str,"mysql");

		return is_int($res);
	}
}
