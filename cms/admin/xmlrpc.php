<?php
include("../common/common.inc.php");
include('webapp/config.inc.php');
ob_start();
SOY2::import("util.CMSUtil");

$serverImpl = new BloggerServerImpl();
$metaBlogServerImpl = new MetaBlogServerImpl();
$mtServerImpl = new MovableTypeServerImpl();

$functions = array(
	"blogger.getUsersBlogs" => array(&$serverImpl, "getUsersBlog"),
	"blogger.getCategoryList" => array(&$serverImpl, "getCategoryList"),
	"blogger.getRecentsPosts" => array(&$serverImpl, "getRecentsPosts"),
	"blogger.deletePost" => array(&$metaBlogServerImpl, "deletePost"),
	"blogger.editPost" => array(&$serverImpl, "editPost"),
	"blogger.getUserInfo " => array(&$serverImpl, "getUserInfo"),
	"blogger.getTemplate " => array(&$serverImpl, "getTemplate"),

	//metaWeblogAPI
	"metaWeblog.newPost" => array(&$metaBlogServerImpl, "newPost"),
	"metaWeblog.editPost" => array(&$metaBlogServerImpl, "editPost"),
	"metaWeblog.getPost"=> array(&$metaBlogServerImpl, "getPost"),
	"metaWeblog.getCategories" => array(&$metaBlogServerImpl, "getCategories"),
	"metaWeblog.getRecentPosts" => array(&$metaBlogServerImpl, "getRecentPosts"),
	"metaWeblog.newMediaObject"=> array(&$metaBlogServerImpl, "newMediaObject"),

	//MovableTypeAPI
	"mt.getCategoryList" => array(&$mtServerImpl, "getCategoryList"),
	"mt.getPostCategories" => array(&$mtServerImpl, "getPostCategories"),
	"mt.setPostCategories" => array(&$mtServerImpl, "setPostCategories"),
	"mt.publishPost" => array(&$mtServerImpl, "publishPost")
);

//PATHINFOからブログを特定
//ex) xmlrpc.php/サイトID/URL
$pathinfo = @$_SERVER["PATH_INFO"];
if($pathinfo[0]=="/")$pathinfo = substr($pathinfo,1);
if(strpos($pathinfo,"/")){
	$siteid = substr($pathinfo,0,strpos($pathinfo,"/"));
	$url = substr($pathinfo,strpos($pathinfo,"/")+1);
}else{
	$siteid = $pathinfo;
	$url = "";
}
if($url === false)$url = "";

ini_set("mbstring.language","Japanese");

/* 準備終了 */

$server = new SimpleXMLRPCServer();

if(!$serverImpl->check($siteid,$url)){
	header('Content-Type: text/xml charset=utf-8');
	$response = $server->encode($serverImpl->error("1","invaild"));
	echo $response;
	exit;
}

//同期を取る
ServerBase::syncParameters($serverImpl,$metaBlogServerImpl,$mtServerImpl);

if(false){
	$server = xmlrpc_server_create();
	foreach($functions as $method => $value){
		xmlrpc_server_register_method($server, $method, $value);
	}
	
	$xml = file_get_contents('php://input');
	
	$response = xmlrpc_server_call_method($server,$xml,null,array(
		"encoding" => "UTF-8",
		"escaping" => array("")
	));
	
	header('Content-Type: text/xml charset=utf-8');
	echo $response;
	file_put_contents("log2.log",$response);

}else{

	//call function
	header('Content-Type: text/xml charset=utf-8');
	$response = $server->listen($functions);
	$html = ob_get_contents();
	ob_end_clean();
	
	echo $html;
}
exit;

class SimpleXMLRPCServer{

	protected $methodName;

	/**
	 * toXml
	 */
	function encode($array){

		$xml = array();
		$xml[] = '<?xml version="1.0" encoding="UTF-8" ?>';
		$xml[] = '<methodResponse>';

		//エラーの場合
		if(is_array($array) && isset($array["faultCode"])){

			$xml[] = '<fault>';
			$xml[] = $this->_encode($array);
			$xml[] = '</fault>';

		}else{
			$array = array($array);

			$xml[] = '<params>';
			foreach($array as $value){
				$xml[] = '<param>';
				$xml[] = $this->_encode($value);
				$xml[] = '</param>';
			}
			$xml[] = '</params>';
		}

		$xml[] = '</methodResponse>';

		return implode("\n",$xml);
	}

	function _encode($value){
		if(!is_array($value)){
			return $this->encodeValue($value);

		//配列の場合
		}else if(is_array($value) && range(0,count($value)-1) === array_keys($value)){
			return $this->encodeArrayValue($value);

		//空の配列
		}else if(is_array($value) && count($value) == 0){
			return $this->encodeArrayValue($value);

		//おそらく連想配列
		}else{
			return $this->encodeStructValue($value);
		}
	}

	function encodeValue($value){

		if(strlen($value) || $value === false){

			$type = gettype($value);

			switch($type){
				case "boolean":
					return '<value><boolean>' . (int)$value . '</boolean></value>';
					break;
				case "integer":
					return '<value><i4>' . $value . '</i4></value>';
					break;
				case "float":
					return '<value><float>' . (float)$value . '</float></value>';
					break;
				case "double":
					return '<value><double>' . (double)$value . '</double></value>';
					break;
			}

			if(strtotime($value) !== false){
				return '<value><dateTime.iso8601>' . $value . '</dateTime.iso8601></value>';
			}

			if(strpos($value,"<") || strpos($value,">")){
				$value = "<![CDATA[" . $value . "]]>";
			}

			return '<value><string>' . $value . '</string></value>';
		}else{
			return '<value><string /></value>';
		}
	}

	function encodeArrayValue($array){
		$res = array();

		if(!empty($array)){
			$res[] = '<value>';
			$res[] = '<array>';

			$res[] = '<data>';
			foreach($array as $key => $value){
				$res[] = $this->_encode($value);
			}
			$res[] = '</data>';
			$res[] = '</array>';
			$res[] = '</value>';
		}else{
			$res[] = '<value><array><data /></array></value>';
		}

		return implode("\n",$res);
	}

	function encodeStructValue($obj){

		$res = array();
		$res[] = '<value>';

		$res[] = '<struct>';
		foreach($obj as $key => $value){
			$res[] = '<member>';
			$res[] = '<name>' . $key . '</name>';
			$res[] = $this->_encode($value);
			$res[] = '</member>';
		}
		$res[] = '</struct>';

		$res[] = '</value>';

		return implode("\n",$res);

	}

	/**
	 * サーバとして振舞う
	 * @return xml
	 */
	function listen($functions){
		$xml = file_get_contents('php://input');
		$xmlobj = @simplexml_load_string($xml);

		if($xmlobj == false){
			return $this->methodNotFound("Invalid XML");
		}

		//メソッド名の取得
		$methodName = (string)$xmlobj->methodName;
		$this->methodName = $methodName;

		//メソッドが実装されていない
		if(!isset($functions[$methodName])){
			return $this->methodNotFound($methodName);
		}

		$params = $this->decode($xmlobj);
		$res = call_user_func($functions[$methodName],$methodName,$params,null);

		$response = $this->encode($res);

		echo $response;
	}

	/**
	 * メソッドがない
	 */
	function methodNotFound($methodName){

		$array = array(
			"faultCode" => "-32601",
			"faultString" => $methodName . " is not found"
		);

		echo $this->encode($array);

	}

	/**
	 * toObj
	 */
	function decode($xmlobj){

		$res = array();

		$array = $xmlobj->params->param;

		foreach($array as $obj){
			$value = $obj->value;
			$res[] = $this->_decode($value);

		}

		return $res;
	}

	function _decode($value){

		if(isset($value->struct)){
			return $this->decodeStruct($value->struct);
		}else if(isset($value->array)){
			return $this->decodeArray($value->array);
		}else{
			return $this->decodeValue($value);
		}

	}

	function decodeStruct($struct){
		$array = $struct->member;

		$res = array();
		foreach($array as $member){

			$name = (string)$member->name;
			$value = $this->_decode($member->value);

			$res[$name] = $value;
		}

		return $res;
	}

	function decodeArray($array){
		$res = array();
		$array = $array->data->value;

		foreach($array as $value){
			$res[] = $this->_decode($value);
		}
		return $res;
	}

	function decodeValue($value){

		$value = (array)$value;

		if(isset($value["string"])){
			return (string)$value["string"];
		}

		if(isset($value["int"])){
			return (int)$value["int"];
		}

		if(isset($value["i4"])){
			return (int)$value["i4"];
		}

		if(isset($value["double"])){
			return (double)$value["double"];
		}

		if(isset($value["boolean"])){
			return (boolean)$value["boolean"];
		}

		if(isset($value["base64"])){
			$obj = new stdClass;
			$obj->scalar = (string)base64_decode($value["base64"]);
			$obj->xmlrpc_type = "base64";
			return $obj;
		}

		if(isset($value["dateTime.iso8601"])){
			return $value["dateTime.iso8601"];
		}

		return (string)$value;

	}

	function trace($obj){
		SOY2Debug::trace($obj);
	}
}

class ServerBase{

	protected $user;	//Adminsitrator
	protected $site;	//Site
	protected $blog;	//Blog
	protected $role;	//SiteRole

	/**
	 * @return boolean
	 */
	function check($siteId,$blogPageUrl){

		//サイトがなかったらアウト
		try{
			$this->site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
			$this->connectSiteDsn();
		}catch(Exception $e){
			return false;
		}

		//ページがなくてもいい
		try{
			$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
			$page = $pageDAO->getByUri($blogPageUrl);

			if(strlen($blogPageUrl)<1 && !$page->isBlog()){
				return true;
			}

		}catch(Exception $e){
			return true;
		}

		//ブログでなかったらアウト
		try{
			$this->blog = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($page->getId());
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * ログイン実行
	 * @return boolean
	 */
	function login($userid, $password){
		$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");

		$oldDsn = SOY2DAOConfig::Dsn();
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

		try{
			//SOYCMSにログイン
			$res = $logic->login($userid,$password);
			
			if(!$res)throw new Exception();

			$this->user = $logic;

			//ディフォルトユーザ
			if($this->user->getIsDefaultUser()){
				$this->connectSiteDsn();
				return true;
			}

			//サイトの権限チェック
			$siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");
			$siteRole = $siteRoleDao->getSiteRole($this->site->getId(),$this->user->getId());
			$this->role = $siteRole;

			$this->connectSiteDsn();

			return true;
		}catch(Exception $e){
			return false;
		}
	}

	function connectSiteDsn($site = null){
		$site = $this->site;

		if(SOYCMS_DB_TYPE == "mysql"){
			SOY2DAOConfig::Dsn($site->getDataSourceName());
		}else{
			SOY2DAOConfig::Dsn("sqlite:".$site->getPath().".db/sqlite.db");
		}
	}

	function trace($obj){
		SOY2Debug::trace($obj);
	}

	/**
	 * XMLRPCエラーを返す
	 */
	function error($id,$str){
		return array(
			"faultCode" => $id,
			"faultString" => $str
		);
	}

	/**
	 * 同期を取る
	 */
	public static function syncParameters(){

		$args = func_get_args();

		if(count($args)<1)return;

		$server = $args[0];
		for($i=1;$i<count($args);$i++){
			$args[$i]->blog = $server->blog;
			$args[$i]->site = $server->site;
		}

	}

}

class BloggerServerImpl extends ServerBase{

	/**
	 * ユーザのブログ一覧を返すとかいう
	 */
	function getUsersBlog($params, $args, $user_data){

		if(count($args)<3){
			return array('faultCode'=>0, 'faultString'=>'なんかダメ');
		}

		//ユーザ名とパスワードを取得
		$userId = $args[1];
		$pass = $args[2];

		$blogs = array();
		if($this->login($userId,$pass)){

			if($this->blog){
				$blog = $this->blog;
				$url = $this->site->getUrl() . $blog->getUri();
				$blogid = $blog->getId();
				$blogName = $blog->getTitle();
			}else{
				$url = $this->site->getUrl();
				$blogid = "*";
				$blogName = $this->site->getSiteName();
			}

			$blogs[] = array(
				"url" => $url,
				"blogid" => $blogid,
				"blogName" => $blogName
			);
		}

		return $blogs;
	}

	/**
	 * カテゴリ一覧をかえすとかいう
	 */
	function getCategoryList($params, $args, $user_data){
		return $this->error(-1,"not impelmented");
	}



	/**
	 * カテゴリを設定するとかいう
	 */
	function setPostCategories($params, $args, $user_data){
		return $this->error(-1,"not impelmented");
	}
}

class MetaBlogServerImpl extends ServerBase{

	/**
	 * エントリーを投稿するとかいう
	 */
	function newPost($params, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];
		$content = @$args[3];
		$isPublish = @$args[4];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		try{

			$entryDAO = SOY2DAOFactory::create("cms.EntryDAO");
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$entry = new Entry();

			$entry->setTitle($content["title"]);
			$entry->setContent($content["description"]);
			if(isset($content["mt_text_more"]))$entry->setMore($content["mt_text_more"]);
			if(isset($content["dateCreated"]) && strtotime($content["dateCreated"] !== false))$entry->setCdate(strtotime($content["dateCreated"]));

			$entry->setOpenPeriodEnd(CMSUtil::encodeDate($entry->getOpenPeriodEnd(),false));
			$entry->setOpenPeriodStart(CMSUtil::encodeDate($entry->getOpenPeriodStart(),true));

			if($isPublish && $this->role->isEntryPublisher()){
				$entry->setIsPublished(true);
			}else{
				$entry->setIsPublished(false);
			}

			$entryId = $logic->create($entry);

			//ブログに投稿出来るようにラベルを関連づける。
			$entryLabelDAO = SOY2DAOFactory::create("cms.EntryLabelDAO");
			$entryLabel = new EntryLabel();
			$entryLabel->setEntryId($entryId);
			if($this->blog)$entryLabel->setLabelId($this->blog->getBlogLabelId());
			$entryLabelDAO->insert($entryLabel);

			//カテゴリ指定があった場合はカテゴリもつける
			$categories = @$content["categories"];
			if(is_array($categories)){
				$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
				foreach($categories as $category){
					try{
						$label = $labelDAO->getByCaption($category);
						$entryLabelDAO->setByParams($id,$label->getId());
					}catch(Exception $e){
						//
					}
				}
			}

			//entryid
			return $entryId;

		}catch(Exception $e){
			return $this->error(1,$e->getMessage());
		}

	}

	/**
	 * エントリーを上書き保存するとかいう
	 */
	function editPost($params, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];
		$content = @$args[3];
		$isPublish = @$args[4];

		$content = $args[3];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		try{
			$entryDAO = SOY2DAOFactory::create("cms.EntryDAO");
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$entry = $entryDAO->getById($id);
			
			if(!$this->role->isEntryPublisher()
				&& $entry->isActive() > 0
			){
				return $this->error("2","can not update open entry");
			}

			if(isset($content["title"]))$entry->setTitle($content["title"]);
			if(isset($content["description"]))$entry->setContent($content["description"]);
			if(isset($content["mt_text_more"]))$entry->setMore($content["mt_text_more"]);
			if(isset($content["dateCreated"]) && strtotime($content["dateCreated"] !== false))$entry->setCdate(strtotime($content["dateCreated"]));
			$entry->setUdate(time());
			$entry->setIsPublished($isPublish);

			$logic->update($entry);

			$categories = @$content["categories"];

			if(is_array($categories)){
				$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
				$entryLabelDAO = SOY2DAOFactory::create("cms.EntryLabelDAO");
				$entryLabelDAO->deleteByEntryId($id);
				if($this->blog)$entryLabelDAO->setByParams($id,$this->blog->getBlogLabelId());

				foreach($categories as $category){
					try{
						$label = $labelDAO->getByCaption($category);
						$entryLabelDAO->setByParams($id,$label->getId());
					}catch(Exception $e){
						//
					}
				}
			}

		}catch(Exception $e){
			//
		}

		return true;
	}

	/**
	 * エントリーを取得するとかいう
	 *
	 * @see http://msdn.microsoft.com/ja-jp/library/aa905669.aspx
	 * string postid;
	 * DateTime dateCreated;
	 * string title;
	 * string description;
	 * string[] categories;
	 * bool publish;
	 *
	 */
	function getPost($parms, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];
		$content = @$args[3];
		$isPublish = @$args[4];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		$res = array();
		try{
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$entry = $logic->getById($id);

			$res["postid"] = (int)$entry->getId();
			$res["dateCreated"] = date('Ymd\TH:i:s',$entry->getCdate());
			$res["title"] = $entry->getTitle();
			$res["description"] = $entry->getContent();
			$res["publish"] = (boolean)$entry->getIsPublished();
			$res["mt_excerpt"] = $entry->getContent();
			$res["mt_text_more"] = $entry->getMore();

			if($this->blog){
				$url = $this->site->getUrl();
				if(strlen($this->blog->getUri())>0)$url .= $this->blog->getUri() . "/";
				$url .= $this->blog->getEntryPageUri() . "/" . rawurlencode($entry->getAlias());

				$res["link"] = $res["permaLink"] = $url;
			}

			$res["categories"] = array();
			$labels = $entry->getLabels();

			if(count($labels) > 0){
				$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
				foreach($labels as $key => $labelId){

					if($this->blog && $labelId == $this->blog->getBlogLabelId()){
						continue;
					}

					try{
						$label = $labelDAO->getById($labelId);
						$res["categories"][] = $label->getCaption();
					}catch(Exception $e){
						//
					}
				}
			}

			return $res;

		}catch(Exception $e){
			return $this->error("2","invalid id");
		}
	}

	/**
	 * 最近のエントリーを取得するとかいう
	 *
	 * @see http://msdn.microsoft.com/ja-jp/library/aa905674.aspx
	 * @param string blogid,string username,string password,int numberOfPosts
	 * @return
	 * 		string postid;
	 *		DateTime dateCreated;
	 *		string title;
	 * 		string description;
	 * 		string[] categories;
	 * 		bool publish;
	 *
	 */
	function getRecentPosts($parms, $args, $user_data){

		$id = $args[0];
		$userid = $args[1];
		$pass = $args[2];
		$count = (int)$args[3];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		$result = array();
		try{

			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$logic->setLimit($count);

			if($this->blog){
				$entries = $logic->getByLabelId($this->blog->getBlogLabelId());
			}else{
				$entries = $logic->get();
			}

			foreach($entries as $entry){

				$res = array();

				$res["postid"] = (int)$entry->getId();
				$res["dateCreated"] = date('Ymd\TH:i:s',(int)$entry->getCdate());
				$res["title"] = $entry->getTitle();
				$res["description"] = $entry->getContent();
				$res["publish"] = (boolean)$entry->getIsPublished();

				$res["categories"] = array();

				$labels = $entry->getLabels();

				if(count($labels) > 0){
					$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
					foreach($labels as $key => $labelId){

						if($this->blog && $labelId == $this->blog->getBlogLabelId()){
							continue;
						}

						try{
							$label = $labelDAO->getById($labelId);
							$res["categories"][] = $label->getCaption();
						}catch(Exception $e){
							//
						}
					}
				}

				$result[] = $res;
			}

			return $result;

		}catch(Exception $e){
			return $this->error("2","invalid id");
		}
	}

	/**
	 * ファイルをアップロードするとかいう
	 */
	function newMediaObject($parms, $args, $user_data){

		$blogid = $args[0];
		$userid = $args[1];
		$pass = $args[2];
		$array = $args[3];

		$name = str_replace("/","_",$array["name"]);
		$type = $array["type"];
		$bits =  $array["bits"]->scalar;
		$encode = $array["bits"]->xmlrpc_type;

		//base64以外…あるのかどうかは知らないけど。
		//base64デコードはしなくていいみたい。してくれてるのかな。
		if($encode == "base64"){
			//$bits = base64_decode($bits);
		}

		//失敗
		if(strstr("image/",$type) == -1){
			return $this->error("4","invalid data type");
		}

		try{
			//ファイルサイズはPHP任せ

			SOY2::import("util.CMSFileManager");

			//siteconfig
			$defaultUploadDirectory = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getDefaultUploadDirectory();
			$root = $this->site->getPath();

			$targetDir = realpath($root . $defaultUploadDirectory);
			$file = CMSFileManager::get($root,$targetDir);

			//拡張子チェック
			$pathinfo = pathinfo($name);

			if(!isset($pathinfo["extension"]) || !in_array(strtolower($pathinfo["extension"]),CMSFileManager::getAllowedExtensions())){
				throw new Exception("invalid file type");
			}

			$filepath = $file->getPath() . "/" . $name;

			file_put_contents($filepath, $bits);
			chmod($filepath,0604);

			CMSFileManager::add($filepath);

			//URLを構築
			$url = $this->site->getUrl();
			$fileUrl = $defaultUploadDirectory . "/" . $name;
			if($url[strlen($url)-1] != "/")$url .= "/";
			if($fileUrl[0] == "/")$fileUrl = substr($fileUrl,1);
			$url = $url . $fileUrl;

			//成功したらURLを返す
			return array("url" => $url);

		}catch(Exception $e){
			return $this->error("3",$e->getMessage());
		}
	}

	/**
	 * エントリーの削除
	 *
	 */
	function deletePost($parms, $args, $user_data){

		$appkey = @$args[0];
		$id = @$args[1];
		$userid = @$args[2];
		$pass = @$args[3];
		$content = @$args[4];
		$isPublish = @$args[5];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		$res = array();
		try{
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			return $logic->deleteByIds(array($id));

		}catch(Exception $e){
			return $this->error("2","invalid id");
		}

	}

	/**
	 * カテゴリ取得するとかいう
	 *
	 * @see http://msdn.microsoft.com/ja-jp/library/aa905667.aspx
	 */
	function getCategories($parms, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");

		$res = array();

		if($this->blog){
			foreach($this->blog->getCategoryLabelList() as $key => $label){
				$label = $labelDAO->getById($label);

				$res[] = array(
					"title" => $label->getCaption(),
					"description" => $label->getDescription()
				);
			}
		}else{
			$labels = $labelDAO->get();
			foreach($labels as $label){
				$res[] = array(
					"title" => $label->getCaption(),
					"description" => $label->getDescription()
				);
			}
		}

		return $res;
	}
}

class MovableTypeServerImpl extends ServerBase{

	/**
	 * カテゴリを取得
	 */
	function getCategoryList($parms, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");

		$res = array();

		if($this->blog){
			foreach($this->blog->getCategoryLabelList() as $key => $label){
				$label = $labelDAO->getById($label);

				$res[] = array(
					"categoryId" => $label->getId(),
					"categoryName" => $label->getCaption()
				);
			}
		}else{
			$labels = $labelDAO->get();
			foreach($labels as $label){
				$res[] = array(
					"categoryId" => $label->getId(),
					"categoryName" => $label->getCaption()
				);
			}
		}

		return $res;
	}


	/**
	 * 記事IDを指定してカテゴリ取得
	 *
	 * @see http://www.na.rim.or.jp/~tsupo/program/blogTool/mt_xmlRpc.html
	 */
	function getPostCategories($parms, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		$res = array();
		try{
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$entry = $logic->getById($id);

			$res = array();
			$labels = $entry->getLabels();

			if(count($labels) > 0){
				$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
				foreach($labels as $key => $labelId){

					if($this->blog && $labelId == $this->blog->getBlogLabelId()){
						continue;
					}

					try{
						$label = $labelDAO->getById($labelId);
						$res[] = array(
							"categoryId" => $label->getId(),
							"categoryName" => $label->getCaption(),
							"isPrimary" => false
						);

					}catch(Exception $e){
						//

					}
				}
			}

			//ubicast blogger 対策
			if(empty($res)){
				$res[] = array(
					"categoryId" => "no label",
					"categoryName" => "(未選択)",
					"isPrimary" => true
				);
			}

			return $res;

		}catch(Exception $e){
			return $this->error("2","invalid id");
		}

	}

	/**
	 * 記事IDを指定してカテゴリ設定
	 *
	 * @see http://www.na.rim.or.jp/~tsupo/program/blogTool/mt_xmlRpc.html
	 */
	function setPostCategories($parms, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];
		$categories = @$args[3];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		try{

			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$entry = $logic->getById($id);

			if(is_array($categories)){
				$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
				$entryLabelDAO = SOY2DAOFactory::create("cms.EntryLabelDAO");
				$entryLabelDAO->deleteByEntryId($id);
				if($this->blog)$entryLabelDAO->setByParams($id,$this->blog->getBlogLabelId());

				foreach($categories as $category){
					try{
						$label = $labelDAO->getById($category["categoryId"]);
						$entryLabelDAO->setByParams($id,$label->getId());
					}catch(Exception $e){
						//
					}
				}
			}

			return true;

		}catch(Exception $e){
			return $this->error("2","invalid id");
		}

	}

	/**
	 * 記事IDを指定して公開に設定
	 *
	 * @see http://www.na.rim.or.jp/~tsupo/program/blogTool/mt_xmlRpc.html
	 */
	function publishPost($parms, $args, $user_data){

		$id = @$args[0];
		$userid = @$args[1];
		$pass = @$args[2];

		if(!$this->login($userid,$pass)){
			return $this->error("1","login failed");
		}

		try{
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$entry = $logic->getById($id);
			$entry->setIsPublished(1);

			$logic->update($entry);

			return true;
		}catch(Exception $e){
			return false;
		}
	}
}
?>