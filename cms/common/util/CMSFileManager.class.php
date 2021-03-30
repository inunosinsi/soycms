<?php

class CMSFileManager{

	private $allowedExtensions = array(
		"txt",
		"html",
		"htm",

		"jpg",
		"jpeg",
		"png",
		"gif",
		"bmp",
		"swf",
		"ico",

		"js",
		"css",

		"pdf",
		"zip"
	);

	/**
	 * 新しく作られたファイルの情報をDBに追加する
	 */
	public static function insertFile($root, $id, $name){
		if($name[0] == "."){
			return false;
		}

		$file = CMSFileManager::get($root, $id);
		$filepath = $file->getPath() . "/" . $name;

		if(file_exists($filepath)){
			$obj = self::getInstance($root);
			$obj->insert($filepath, $file);
			return true;
		}

		return false;
	}

	/**
	 * 削除されたファイルの情報をDBから消去する
	 */
	public static function removeFile($root, $id){
		$file = CMSFileManager::get($root, $id);
		$dao = self::_getDao();
		$dao->deleteTree($file);

		return $file->getParentFileId();
	}

	public static function getAllowedExtensions(){
		$instance = self::getInstance();
		return $instance->allowedExtensions;
	}

	private function __construct(){
		if(defined("SOYCMS_ALLOWED_EXTENSIONS")){
			$exts = explode(",",SOYCMS_ALLOWED_EXTENSIONS);
			foreach($exts as $ext){
				if(!in_array($ext,$this->allowedExtensions)){
					$this->allowedExtensions[] = $ext;
				}
			}
		}
	}

	/**
	 * もう使ってません
	 */
	public static function Root($root){
	}

	public static function buildAll($root){
		self::deleteAll();
		self::insert($root);

	}

	public static function deleteAll(){
		$dao = self::_getDao();
		$dao->begin();
		try{
			$dao->deleteAll();
		}catch(Exception $e){
			// @Todo MySQL→SQLite移行プラグイン対策
		}
		$dao->commit();
	}

	public static function insertAll($root){
		$dao = self::_getDao();
		$obj = self::getInstance($root);
		$dao->begin();
		try{
			$obj->insert($root);
		}catch(Exception $e){
			// @Todo MySQL→SQLite移行プラグイン対策
		}
		$dao->commit();
	}

	public static function rebuildTree($root, $target){
		try{
			$dao = self::_getDao();
			$dao->begin();

			//$targetが$root以下のファイルであることを確認
			$file = self::get($root, $target, false);
			$parent = ($file->getParentFileId()) ? self::get($root, $file->getParentFileId()) : null ;

			$filepath = $file->getPath();

			//削除
			$dao->deleteChildren($file);

			//再作成
			$obj = self::getInstance($root);
			$dir = $file->getPath();
			if(is_dir($dir)){
				$size = 0;
				$files = scandir($dir);
				foreach($files as $path){
					if($path[0] == ".")continue;
					$size += $obj->insert($dir."/".$path, $file);
				}
				$file->setFileSize($size);
				$dao->update($file);
			}

			$dao->commit();
		}catch(Exception $e){
			$dao->rollback();
			return false;
		}

		return true;
	}

	public static function add($filepath){
		$parentPath = realpath(dirname($filepath));
		$obj = self::getInstance($parentPath);
		$parent = self::get($obj->root,$parentPath);
		$obj->insert($filepath,$parent);
	}

	public static function printList($root,$target){

		$file = self::get($root,$target,true);

		$files = $file->getChildren();

		foreach($files as $file){
			self::printFile($file);
		}
	}

	public static function printFile($file){
		echo '<div class="file_small" ';
		echo 'onclick="showDetail('.$file->getId().',this);"';
		if($file->isDir()){
			echo 'ondblclick="showList(\'file:'.$file->getId().'\');"';
		}else{
			echo 'ondblclick="if(window.parent.filemanager_ondblclick)window.parent.filemanager_ondblclick(\''.$file->getUrl().'\');"';
		}
		echo '>';

		if($file->isImage()){
			echo '<div class="file_icon icon_image">';
			echo '<img src="'.$file->getUrl().'" style="background-image:none;" />';
		}else{
			echo '<div class="file_icon">';

			if($file->isDir()){
				echo '<div class="folder"></div>';
			}else{
				echo '<div class="icon_'.$file->getExtension().'"></div>';
			}
		}
		echo '</div>';

		echo '<div class="file_name">'.mb_strimwidth($file->getName(),0,11,"...").'</div>';

		echo '</div>';
	}

	/**
	 * 処理はprintTreeNodeに移動
	 */
	public static function printTree($root,$target = null){
		return self::printTreeNode($root, true, $target);

	}

	/**
	 * ファイルマネージャの左部ツリーを出力
	 *
	 * @root ルート
	 * @isOutputLi Liタグを出力するかどうか
	 */
	public static function printTreeNode($root,$isOutputLi = false,$target = null){

		$root = str_replace("\\","/",$root);

		if(!$target)$target = $root;

		$file = self::get($root,$target,true);

		if(strstr($file->getPath(),$root) != 0){
			throw new Exception("Wrong path");
		}


		if($isOutputLi){
			$attributes = array();
			$attributes["id"] = "file:".$file->getId();
			$attributes["class"] = "file";
			$attributes["file:id"] = $file->getId();
			$attributes["file:path"] = $file->getUrl();
			$attributes["file:isDir"] = $file->getIsDir();
			$attributes["file:name"] = $file->getName();
			$attributes["file:size"] = $file->getFileSize();
			$attributes["file:update"] = date("Y-m-d H:i:s",$file->getUpdateDate());
			$attributes["file:create"] = date("Y-m-d H:i:s",$file->getCreateDate());
			$attributes["file:isImage"] = $file->getIsImage();

			$attributesText = array();
			foreach($attributes as $key => $value){
				$attributesText[] = $key .'="'.$value.'"';
			}

			//書式考えた方が良い？
			echo "<li ".implode(" ",$attributesText).">";
		}

		echo "<span id=\"file:".$file->getId()."_name\">";
		echo $file->getName();
		echo "</span>";

		$children = $file->getChildren();
		if($children){
			echo "<ul>";
			foreach($children as $child){
				if($child->getIsDir()){
					self::printTree($root,$child);
				}
			}
			echo "</ul>";
		}
		if($isOutputLi)echo "</li>";

		return $file;

	}

	public static function get($root,$target,$withChild = false){
		$file = null;
		if(is_numeric($target)){
			$dao = self::_getDao();
			try{
				$file = $dao->getById($target,$withChild);
			}catch(Exception $e){
				var_dump($e);
				//
			}
		}else if(is_string($target)){
			//targetがhttpから始まる場合はURLの方で取得を試みる
			$dao = self::_getDao();
			if(strpos($target, "http") === 0){
				try{
					$file = $dao->getByUrl($target);
				}catch(Exception $e){
					//
				}
			}else{
				try{
					$target = str_replace("\\","/",$target);
					$file = $dao->getByPath($target,$withChild);
				}catch(Exception $e){
					//FileDBの更新を試みる
					try{
						self::updateFileDb();
						$file = $dao->getByPath($target,$withChild);
					}catch(Exception $e){
						try{
							$target = str_replace("\\","/",realpath($target));
							$file = $dao->getByPath($target,$withChild);
						}catch(Exception $e){
							//
						}
					}
				}
			}
		}else{
			$file = $target;
		}

		if(!$file) throw new Exception("");

		if(strpos($file->getPath(),$root) != 0){
			throw new Exception("Wrong path");
		}

		return $file;
	}

	private static function updateFileDb(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$old = CMSUtil::switchDsn();
		$sites = $SiteLogic->getSiteList();
		CMSUtil::resetDsn($old);

		foreach($sites as $site){
			self::setSiteInformation($site->getId(), $site->getUrl(), $site->getPath());
			self::insertAll($site->getPath());
		}
	}

	public static function getAllFile($root,$target,&$array = array()){
		$dao = self::_getDao();
		$file = $dao->getById($target,false);

		$array[$file->getId()] = $file;

		$files = $dao->getByParentFileId($file->getId());
		foreach($files as $file){
			self::getAllFile($root,$file->getId(),$array);
		}

		return $array;

	}

	public static function printJson($root,$target){
		$self = self::getInstance();

		$file = self::get($root,$target);

		$obj = new stdClass;
		$obj->id = $file->getId();
		$obj->name = $file->getName();
		$obj->path = str_replace($self->getRoot(),"/",$file->getPath());
		$obj->url = $file->getUrl();
		$obj->size = soy2_number_format($file->getFileSize());
		$obj->create = date("Y-m-d H:i:s",$file->getCreateDate());
		$obj->update = date("Y-m-d H:i:s",$file->getUpdateDate());
		$obj->type = $file->getExtension();

		if($file->isDir()){
			$obj->type = "directory";
		}

		if($file->isImage()){
			$obj->type = "image";
		}



		echo json_encode($obj);
	}

	public static function upload($root,$id,$upload){

		//拡張子チェック
		$pathinfo = pathinfo($upload["name"]);
		if(!isset($pathinfo["extension"]) || !in_array(strtolower($pathinfo["extension"]),CMSFileManager::getAllowedExtensions())){
			return "Wrong extention";
		}

		$file = CMSFileManager::get($root,$id);
		if($file->isDir()){

			$filepath = $file->getPath() . "/" . $upload["name"];

			$result = move_uploaded_file($upload["tmp_name"],$filepath);
			@chmod($filepath,0666);

			if($result){
				$obj = self::getInstance($root);
				$id = $obj->insert($filepath,$file);
				return true;//$id;
			}
		}else{
			$result = "Is not directory";
		}

		return $result;
	}

	public static function delete($root,$id){
		$file = CMSFileManager::get($root,$id);
		$dao = self::_getDao();
		$dao->delete($file);

		return $file->getParentFileId();
	}

	public static function makeDirectory($root, $id, $name){

		if($name[0] == ".")return false;

		$file = CMSFileManager::get($root, $id);
		$filepath = $file->getPath() . "/" . $name;
		mkdir($filepath);
		if(file_exists($filepath)){
			$obj = self::getInstance($root);
			$obj->insert($filepath, $file);
			return true;
		}

		return false;
	}

	public static function search($queries){

		$where = array();
		$extensionbinds = array();
		$namebinds = array();

		foreach($queries as $query){
			$array = explode("|",$query);
			$ors = array();
			foreach($array as $condition){
				$conditions = explode(":",$condition);
				if(count($conditions)>1){
					$type = strtolower($conditions[0]);
					switch($type){
						case "isdir":
							$ors[] = "is_dir = ".(($conditions[1]) ? 1 : 0);
							break;
						case "isimage":
							$ors[] = "is_image = ".(($conditions[1]) ? 1 : 0);
							break;
						case "ext":
							$key = ":extension" . count($extensionbinds);
							$ors[] = "extension = $key";
							$extensionbinds[$key] = $conditions[1];
							break;
						default:
							$key = ":name" . count($namebinds);
							$ors[] = "name like $key";
							$namebinds[$key] = "%".$condition."%";
							break;
					}
				}else{
					$key = ":name" . count($namebinds);
					$ors[] = "name like $key";
					$namebinds[$key] = "%".$condition."%";
				}
			}
			$where[] = "(" . implode(" OR ",$ors) . ")";
		}

		$where = implode(" AND ",$where);

		$where .= " AND path like :path";
		$namebinds[":path"] = UserInfoUtil::getSiteDirectory() . "%";

		$dao = self::_getDao();
		$result = $dao->search($where,($extensionbinds + $namebinds));

		if(count($result)<1){
			echo CMSMessageManager::get("SOYCMS_FILEMANAGER_NOTFOUND");
		}

		foreach($result as $file){
			self::printFile($file);
		}
	}

	public static function debug(){
		$dao = self::_getDao();
	}

	/**
	 * SiteのURLとパスを設定
	 * URLの生成に使う
	 *
	 * @param $siteId
	 * @param $siteUrl
	 * @param $sitePath
	 */
	public static function setSiteInformation($siteId, $siteUrl, $sitePath){
		$inst = self::getInstance();
		$inst->setSiteUrl($siteUrl);
		$inst->setSiteId($siteId);
		$inst->setSiteRoot($sitePath);
	}


	/**
	 * @singleton
	 */
	private static function &getInstance($root = SOYCMS_TARGET_DIRECTORY){
		static $obj;
		if(!$obj){
			$obj = new CMSFileManager();
			$obj->setRoot($root);

			$site = UserInfoUtil::getSite();
			if($site){
				self::setSiteInformation($site->getId(), $site->getUrl(), $site->getPath());
			}
		}
		return $obj;
	}

	/* DAOの取得 */
	private static function &_getDao(){
		static $obj;
		if(!$obj)$obj = SOY2DAOFactory::create("CMSFileDAO");
		return $obj;
	}

	private function getDao(){
		return self::_getDao();
	}

	/* 以下、内部使用のメソッド */

	private $root;

	private $siteId;
	private $siteUrl;
	private $siteRoot;

	/**
	 * @return CMSFile
	 */
	private function insert($target,$parent = null){
		if( ! file_exists($target) ) return 0;

		$dao = $this->getDao();
		$root = $this->getRoot();

		$root = str_replace("\\","/",realpath($root));
		$target = str_replace("\\","/",realpath($target));

		try{
			$file = $dao->getByPath($target);
			return 0;
		}catch(Exception $e){
			$file = new CMSFile();
		}

		$pathinfo = pathinfo($target);
		$file->setName($pathinfo['basename']);
		$file->setPath($target);

		if(defined("SOYCMS_ASP_MODE")){

			$url = str_replace("\\","/",str_replace($root,"",$target));
			if($url[0] == "/") $url = substr($url,1);
			$siteId = preg_replace('/([^\/]+)\/(.*)/','$1',$url);
			$url = preg_replace('/([^\/]+)\/(.*)/','$2',$url);
			$url = preg_replace('/^\/+/',"",$url);
			$url = UserInfoUtil::getSiteURLBySiteId($siteId) . $url;
			$file->setUrl($url);

		}else{

			if($this->getSiteUrl()){
				$siteUrl = $this->getSiteUrl();
				$url = str_replace($this->getSiteRoot(),"",$target);
				if(strlen($url) && $url[0] == "/" && $siteUrl[strlen($siteUrl)-1] == "/")$url = substr($url,1);
				$url = $siteUrl . $url;
			}else{
				$url = str_replace("\\","/",str_replace($root,"",$target));
				if(strlen($url) > 0 && $url[0] != "/")$url = "/".$url;
			}

			$file->setUrl($url);
			if($root == $target){
				$file->setUrl("/");
			}
		}
		$file->setIsDir(is_dir($target));
		$file->setExtension(strtolower(@$pathinfo['extension']));

		if($file->getExtension() == "php")return 0;

		switch(strtolower($file->getExtension())){

			case "gif":
			case "jpeg":
			case "jpg":
			case "png":
				$file->setIsImage(true);
				break;

		}
		if($parent){
			$file->setParentFileId($parent->getId());
		}

		$file->setCreateDate(@filectime($target));
		$file->setUpdateDate(@filemtime($target));

		$id = $dao->insert($file);
		$file->setId($id);

		if(is_dir($target)){
			$files = scandir($target);

			$size = 0;
			foreach($files as $path){
				if($path[0] == ".")continue;
				$size += $this->insert($target."/".$path,$file);
			}
			$file->setFileSize($size);

		}else{
			$file->setFileSize(filesize($target));
		}

		$dao->update($file);

		return $file->getFileSize();
	}

	function getRoot() {
		return $this->root;
	}
	function setRoot($root) {
		$this->root = str_replace("\\","/",realpath($root));
		if(strlen($this->root) && $this->root[strlen($this->root)-1] != "/")$this->root .= "/";
	}

	function setAllowedExtensions($allowedExtensions) {
		$this->allowedExtensions = $allowedExtensions;
	}
	function getSiteUrl() {
		return $this->siteUrl;
	}
	function setSiteUrl($siteUrl) {
		$this->siteUrl = $siteUrl;
	}
	function getSiteRoot() {
		return $this->siteRoot;
	}
	function setSiteRoot($siteRoot) {
		$this->siteRoot = str_replace("\\","/",realpath($siteRoot));;
	}

	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
}

/**
 * @entity CMSFile
 */
abstract class CMSFileDAO extends SOY2DAO{

	abstract function get();

	/**
	 * @final
	 * IDを指定して取得。第二引数をtrueで子を取得（1段階だけ）
	 */
	function getById($id,$withChild = false){
		try{
			$file = $this->_getById($id);
			if($withChild && $file->getIsDir()){
				$files = $this->getByParentFileId($file->getId());
				$file->addChildren($files);
			}
		}catch(Exception $e){
			throw $e;
		}

		return $file;
	}

	/**
	 * @return object
	 */
	abstract function _getById($id);

	/**
	 * @final
	 * パスを指定して取得。第二引数をtrueで子を取得（1段階だけ）
	 */
	function getByPath($path,$withChild = false){
		try{
			$file = $this->_getByPath(str_replace("\\","/",$path));
			if($withChild && $file->getIsDir()){
				$files = $this->getByParentFileId($file->getId());
				$file->addChildren($files);
			}
		}catch(Exception $e){
			throw $e;
		}

		return $file;
	}

	/**
	 * @return object
	 */
	abstract function _getByPath($path);

	/**
	 * @return object
	 */
	abstract function getByUrl($url);


	/**
	 * @columns count(id) as childcount
	 * @query #parentFileId# = :id
	 */
	function hasChildren($id){

		$query = $this->getQuery();
		$binds = $this->getBinds();

		$result = $this->executeQuery($query,$binds);

		if($result[0]["childcount"]){
			return true;
		}
	}

	function search($where,$binds){
		$query = $this->getQuery();
		$query->where = $where;
		$result = $this->executeQuery($query,$binds);

		$return = array();
		foreach($result as $row){
			$return[] = $this->getObject($row);
		}

		return $return;
	}

	/**
	 * @order is_dir,extension,name
	 */
	abstract function getByParentFileId($parentFileId);

	/**
	 * @return id
	 */
	abstract function insert(CMSFile $bean);
	abstract function update(CMSFile $bean);

	/**
	 * 全て削除　開発用？
	 * @query 1 = 1
	 */
	abstract function deleteAll();

	abstract function deleteById($id);

	/**
	 * @final
	 * ファイルも削除する
	 */
	function delete($file){

		if($file->isDir() && $this->hasChildren($file->getId())){
			return;
		}

		$this->begin();
		$this->deleteById($file->getId());
		$path = $file->getPath();

		if($file->isDir()){
			$result = @rmdir($path);
		}else{
			$result = @unlink($path);
		}

		if(!file_exists($path)){
			$this->commit();
		}else if(!$result){
			$this->rollback();
		}
	}

	/**
	 * @final
	 * ファイル情報のみを削除する
	 */
	function deleteTree($file){
		$this->deleteChildren($file);
		$this->deleteById($file->getId());
	}
	/**
	 * @final
	 * 子孫ファイル情報のみを削除する
	 */
	function deleteChildren($file){
		if($file->isDir() && $this->hasChildren($file->getId())){
			$children = $this->getByParentFileId($file->getId());
			foreach($children as $child){
				$this->deleteTree($child);
			}
		}
	}

	/* 以下SOY2DAO使用の関数 */

	/**
	 * @final
	 */
	function &getDataSource(){
		return CMSFileDAO::_getDataSource();
	}

	/**
	 * @final
	 */
	public static function &_getDataSource($__dsn = null,$__user = null, $__pass = null){
		static $pdo;

		if(is_null($pdo)){

			if(defined("SOYCMS_ASP_MODE")){

				$dsn =  SOYCMS_ASP_DSN;
				$user = SOYCMS_ASP_USER;
				$pass = SOYCMS_ASP_PASS;

				try{
					$pdo = new PDO($dsn,$user,$pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				} catch (PDOException $e) {
					die("Can not get DataSource.".$dsn);
				}


			}else{
				$init = false;
				$check = false;

				//MySQL→SQLite移行プラグインを利用した場合
				if(strpos(CMS_FILE_DB, "mysql") === 0 && strpos(SOY2DAOConfig::Dsn(), "sqlite") === 0){
					$dsn = "sqlite:".SOY2::RootDir()."db/file.db";
					$user = "";
					$pass = "";
					$check = true;
				}else{
					$dsn =  CMS_FILE_DB;
					$user = SOY2DAOConfig::user();
					$pass = SOY2DAOConfig::pass();
				}

				if(CMS_FILE_DB_EXISTS != true){
					$init = true;
				}

				try{
					$pdo = new PDO($dsn,$user,$pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				} catch (PDOException $e) {
					die("Can not get DataSource.".$dsn);
				}

				if($check){
					try{
						$pdo->query("SELECT * FROM cmsfile LIMIT 1;");
					}catch(Exception $e){
						$init = true;
					}
				}

				if($init){
					$dbType = (strpos($dsn, "mysql") === 0) ? "mysql" : "sqlite";
					self::init($dbType);
				}
			}
		}

		return $pdo;
	}

	/**
	 * DBの初期化
	 * @final
	 */
	private static function init($dbType){
		$sql = file_get_contents(CMS_SQL_DIRECTORY."init_file_".$dbType.".sql");
		CMSFileDAO::_getDataSource()->exec($sql);

		if(!file_exists(SOY2::RootDir()."/db/file.db")){
			file_put_contents(SOY2::RootDir()."/db/file.db","generated");
		}
	}
}

/**
 * @table cmsfile
 */
class CMSFile{

	/**
	 * @id
	 */
	private $id;

	private $name;
	private $path;
	private $url;

	/**
	 * @column parent_file_id
	 */
	private $parentFileId;

	private $extension;

	/**
	 * @column is_dir
	 */
	private $isDir;

	/**
	 * @column is_image
	 */
	private $isImage;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	/**
	 * @column file_size
	 */
	private $fileSize;

	/**
	 * @no_persistent
	 */
	private $children = array();

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
	function getPath() {
		return $this->path;
	}
	function setPath($path) {
		$this->path = str_replace("\\","/",$path);
	}
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getParentFileId() {
		return $this->parentFileId;
	}
	function setParentFileId($parentFileId) {
		$this->parentFileId = $parentFileId;
	}
	function getExtension() {
		return $this->extension;
	}
	function setExtension($extension) {
		$this->extension = $extension;
	}
	function getIsDir() {
		return (int)$this->isDir;
	}
	function setIsDir($isDir) {
		$this->isDir = $isDir;
	}
	function getIsImage() {
		return $this->isImage;
	}
	function isImage(){
		return $this->getIsImage();
	}
	function isDir(){
		return $this->getIsDir();
	}
	function setIsImage($isImage) {
		$this->isImage = $isImage;
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

	function getChildren() {
		return $this->children;
	}
	function addChildren($children) {
		if(is_array($children)){
			$this->children += $children;
		}else{
			$this->children[] = $children;
		}
	}

	function getFileSize() {
		return $this->fileSize;
	}
	function setFileSize($fileSize) {
		$this->fileSize = $fileSize;
	}
	function setChildren($children) {
		$this->children = $children;
	}
}
