<?php
define('APPLICATION_ID', "voice");
/**
 * ページ表示
 */
class SOYVoice_PageApplication{

	var $page;
	var $serverConfig;
	

	function init(){
		CMSApplication::main(array($this,"main"));
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			return;
		}
	}
	
	function prepare(){
		
	}

	function main($page){
		
		$this->page = $page;
		
		
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();
		
		//設定ファイルの読み込み
		include_once(dirname(__FILE__) . "/config.php");
		$this->prepare();
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		$arguments = CMSApplication::getArguments();
		
		SOY2::import("domain.SOYVoice_Area");

		//app:id="soyvoice"
		$this->page->createAdd("soyvoice","SOYVoice_Component",array(
			"application" => $this,
			"page" => $page,
			"soy2prefix" => "app"
		));
				
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2HTMLConfig::CacheDir($oldCacheDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);

		//画像の置き場を生成
		$path = SOY_VOICE_IMAGE_UPLOAD_DIR;
		if(!is_dir($path))mkdir($path,0755);

	}

}

class SOYVoice_Component extends SOYBodyComponentBase{
	
	private $page;
	private $application;
	private $logic;
	private $value=null;
	
	function doPost(){

		if(soy2_check_token()&&isset($_POST["Voice"])){
			$voice = $_POST["Voice"];
			
			$res = $this->logic->insertVoice($voice);
			
			if($res==false){
				$this->value = $voice;
			}
			
		}
	}
	
	function setPage($page){
		$this->page = $page;
	}
	
	function execute(){
		$this->logic = SOY2Logic::createInstance("logic.PublishLogic");
		$logic = $this->logic;
		
		if(isset($_POST["Voice"])){
			$this->doPost();
		}
		
	
		/**
		 * @Todo
		 * ・新着コメントを表示するためのapp:id
		 * ・投稿フォームを表示するためのapp:id
		 * ・アーカイブを表示するためのapp:id
		 * ・ページャを設置したい
		 */
		 
		switch($this->getAttribute("app:type")){
			case "archive":
				$this->buildArchive();
				break;
			case "new":
			default:
				$this->buildNewVoice();
				$this->buildVoiceForm();
				break;
		}
		
		parent::execute();
	}
	
	function buildNewVoice(){
		$this->createAdd("new_voice_list","NewVoiceList",array(
			"soy2prefix" => "block",
			"list" => $this->logic->getVoices()
		));
	}
	
	function buildVoiceForm(){
		
		$prefix = "cms";
		
		$this->createAdd("error","HTMLModel",array(
			"soy2prefix" => $prefix,
			"visible" => (!is_null($this->value)),
		));
		
		$this->createAdd("voice_form","HTMLForm",array(
			"soy2prefix" => "block",
			"enctype" => "multipart/form-data"
		));
		
		$this->createAdd("nickname","HTMLInput",array(
			"soy2prefix" => $prefix,
			"name" => "Voice[nickname]",
			"value" => @$this->value["nickname"]
		));
		
		$this->createAdd("content","HTMLTextArea",array(
			"soy2prefix" => $prefix,
			"name" => "Voice[content]",
			"value" => @$this->value["content"]
		));
		
		$this->createAdd("url","HTMLInput",array(
			"soy2prefix" => $prefix,
			"name" => "Voice[url]",
			"value" => @$this->value["url"] 
		));
		
		$this->createAdd("email","HTMLInput",array(
			"soy2prefix" => $prefix,
			"name" => "Voice[email]",
			"value" => @$this->value["email"]
		));
		$this->createAdd("prefecture","HTMLSelect",array(
			"soy2prefix" => $prefix,
    		"name" => "Voice[prefecture]",
    		"options" => SOYVoice_Area::getAreas(),
    		"selected" => @$this->value["prefecture"]
    	));
		
	}
	
	function buildArchive(){
		$args = (count($this->page->arguments)>0) ? $this->page->arguments : array();
		$page = null;
		
		SOY2::import("logic.PagerLogic");
		$config = $this->logic->getConfig();
		
		$limit = $config->getArchive();
		if(count($args)>0) $page = (isset($args[0])) ? $args[0] : null;
		if(is_null($page)) $page = 1;
		$offset = ($page - 1) * $limit;
		
		$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
		
		try{
			$total = $dao->count();
			$dao->setLimit($limit);
			$dao->setOffset($offset);
			$voices = $dao->getCommentIsPublished();
		}catch(Exception $e){
			$voices = array();
		}
		
		$this->createAdd("voice_list","NewVoiceList",array(
			"soy2prefix" => "block",
			"list" => $voices
		));
		
		$this->createAdd("no_list","HTMLModel",array(
    		"visible" => count($voices)==0
    	));

    	//ページャー
		$start = $offset;
		$end = $start + count($voices);
		if($end > 0 && $start == 0)$start = 1;

		$pager = new PagerLogic();
		$pager->setPageUrl($this->page->getPageUrl());
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		
		try{
			$this->buildPager($pager);
		}catch(Exception $e){
			//var_dump($e);
		}
	}

	function buildPager(PagerLogic $pager){

		//件数情報表示
		$this->createAdd("count_start","HTMLLabel",array(
			"soy2prefix" => "cms",
			"text" => $pager->getStart()
		));
		$this->createAdd("count_end","HTMLLabel",array(
			"soy2prefix" => "cms",
			"text" => $pager->getEnd()
		));
		$this->createAdd("count_max","HTMLLabel",array(
			"soy2prefix" => "cms",
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$this->createAdd("next_pager","HTMLLink",$pager->getNextParam());
		$this->createAdd("prev_pager","HTMLLink",$pager->getPrevParam());
		$this->createAdd("pager_list","SimplePager",$pager->getPagerParam());
		
		//ページへジャンプ
		$this->createAdd("pager_jump","HTMLForm",array(
			"soy2prefix" => "cms",
			"method" => "get",
			"action" => $pager->getPageURL()."/"
		));
		$this->createAdd("pager_select","HTMLSelect",array(
			"soy2prefix" => "cms",
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
		
	}
	
	function getApplication() {
		return $this->application;
	}
	function setApplication($application) {
		$this->application = $application;
	}
}

class NewVoiceList extends HTMLList{
	
	protected function populateItem($entity){
		
		$prefix = "cms";
		
		//マスターだった場合
		if($entity->getUserType()==0){
			$nickname = "<span style=\"color:#ff0000;\">".$entity->getNickname()."</span>";
		}else{
			$nickname = $entity->getNickname();
		}
		
		$this->createAdd("nickname","HTMLLabel",array(
			"soy2prefix" => $prefix,
			"html" => $nickname
		));
		
		$this->createAdd("url","HTMLLink",array(
			"soy2prefix" => $prefix,
			"link" => $entity->getUrl(),
			"text" => $entity->getUrl()
		));
		$this->createAdd("email","HTMLLink",array(
			"soy2prefix" => $prefix,
			"link" => "mailto:".$entity->getEmail(),
		));
		$this->createAdd("prefecture","HTMLLabel",array(
			"soy2prefix" => $prefix,
			"text" => SOYVoice_Area::getAreaText($entity->getPrefecture())
		));
		
		$this->createAdd("content","HTMLLabel",array(
			"soy2prefix" => $prefix,
			"html" => nl2br($entity->getContent())
		));
		$this->createAdd("image","HTMLImage",array(
			"soy2prefix" => $prefix,
			"src" => SOY_VOICE_IMAGE_ACCESS_PATH.$entity->getImage(),
			"visible" => (!is_null($entity->getImage())) 
		));
		$this->createAdd("comment_date","DateLabel",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getCommentDate(),
			"format" => "Y-m-d H:i:s"
		));
		
		$reply = soy2_unserialize($entity->getReply());
		
		$this->createAdd("is_reply","HTMLModel",array(
			"soy2prefix" => $prefix,
			"visible" => (is_array($reply))
		));
		
		$this->createAdd("reply_author","HTMLLabel",array(
			"soy2prefix" => $prefix,
			"html" => (isset($reply["author"])) ? "<span style=\"color:#ff0000;\">".$reply["author"]."</span>" : null
		));
		$this->createAdd("reply_content","HTMLLabel",array(
			"soy2prefix" => $prefix,
			"text" => (isset($reply["content"])) ? nl2br($reply["content"]) : null
		));
		$this->createAdd("reply_date","DateLabel",array(
			"soy2prefix" => $prefix,
			"text" => (isset($reply["date"])) ? $reply["date"] : null,
			"format" => "Y-m-d H:i:s"
		));
	}
}

$app = new SOYVoice_PageApplication();
$app->init();

?>